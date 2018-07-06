<?php

/*
 * This file is part of ProgPilot, a static analyzer for security
 *
 * @copyright 2017 Eric Therond. All rights reserved
 * @license MIT See LICENSE at the root of the project for more info
 */


namespace progpilot\Transformations\Php;

use PHPCfg\Block;
use PHPCfg\Func;
use PHPCfg\Op;
use PHPCfg\Script;
use PHPCfg\Visitor;

use progpilot\Objects\MyFunction;
use progpilot\Objects\MyBlock;
use progpilot\Objects\MyDefinition;
use progpilot\Objects\MyExpr;
use progpilot\Objects\MyClass;
use progpilot\Objects\MyOp;
use progpilot\Objects\MyFile;

use progpilot\Code\MyInstruction;
use progpilot\Code\Opcodes;

use progpilot\Transformations\Php\FuncCall;
use progpilot\Transformations\Php\Expr;
use progpilot\Transformations\Php\Assign;
use progpilot\Transformations\Php\Common;
use progpilot\Transformations\Php\Context;

class Transform implements Visitor
{
    private $s_blocks;
    private $context;
    private $block_if_to_be_resolved;

    public function __construct()
    {
        $this->s_blocks = new \SplObjectStorage;
        $this->block_if_to_be_resolved = [];
    }

    public function set_context($context)
    {
        $this->context = $context;
    }

    public function enterScript(Script $script)
    {
        $this->context->outputs->current_includes_file = [];
    }

    public function leaveScript(Script $script)
    {
        // creating edges for myblocks structure as block structure
        foreach ($this->s_blocks as $block) {
            $myblock = $this->s_blocks[$block];
            foreach ($block->parents as $block_parent) {
                if ($this->s_blocks->contains($block_parent)) {
                    $myblock_parent = $this->s_blocks[$block_parent];
                    $myblock->addParent($myblock_parent);
                }
            }
        }

        foreach ($this->block_if_to_be_resolved as $block_resolved) {
            $instruction_if = $block_resolved[0];
            $block_if = $block_resolved[1];
            $block_else = $block_resolved[2];

            if ($this->s_blocks->contains($block_if) && $this->s_blocks->contains($block_else)) {
                $myblock_if = $this->s_blocks[$block_if];
                $myblock_else = $this->s_blocks[$block_else];

                $instruction_if->add_property(MyInstruction::MYBLOCK_IF, $myblock_if);
                $instruction_if->add_property(MyInstruction::MYBLOCK_ELSE, $myblock_else);
            }
        }
    }

    public function enterBlock(Block $block, Block $prior = null)
    {
        $this->inside_include = false;
        if (!($this->context->get_current_op() instanceof Op\Expr\Include_)) {
            $myblock = new MyBlock;
            $myblock->set_start_address_block(count($this->context->get_current_mycode()->get_codes()));
            $this->context->set_current_block($block);

            $this->s_blocks[$block] = $myblock;

            $inst_block = new MyInstruction(Opcodes::ENTER_BLOCK);
            $inst_block->add_property(MyInstruction::MYBLOCK, $myblock);
            $this->context->get_current_mycode()->add_code($inst_block);

            // block is for himself a parent block (handle dataflow for first block)
            $block->addParent($block);

            $myblock->set_id(rand());
        } else {
            $this->inside_include = true;
        }
    }

    public function skipBlock(Block $block, Block $prior = null)
    {
    }

    public function leaveOp(Op $op, Block $block)
    {
    }

    public function leaveBlock(Block $block, Block $prior = null)
    {
        if (!$this->inside_include) {
            if ($this->s_blocks->contains($block)) {
                $myblock = $this->s_blocks[$block];

                $address_end_block = count($this->context->get_current_mycode()->get_codes());
                $myblock->set_end_address_block($address_end_block);

                $inst_block = new MyInstruction(Opcodes::LEAVE_BLOCK);
                $inst_block->add_property(MyInstruction::MYBLOCK, $myblock);
                $this->context->get_current_mycode()->add_code($inst_block);
            }
        }
    }

    public function enterFunc(Func $func)
    {
        // blocks are set back to zero when entering new function
        $this->context->set_current_block(null);

        $myfunction = new MyFunction($func->name);
        $this->context->set_current_mycode($myfunction->get_mycode());

        $inst_func = new MyInstruction(Opcodes::ENTER_FUNCTION);
        $inst_func->add_property(MyInstruction::MYFUNC, $myfunction);
        $this->context->get_current_mycode()->add_code($inst_func);

        if (!is_null($func->class)) {
            $class_name = $func->class->value;
            // at this moment class is defined
            $myclass = $this->context->get_classes()->get_myclass($class_name);

            if (!is_null($myclass)) {
                $myclass->add_method($myfunction);

                $myfunction->set_visibility(Common::get_type_visibility($func->flags));
                $myfunction->add_type(MyFunction::TYPE_FUNC_METHOD);
                $myfunction->set_myclass($myclass);

                if (($func->flags & Func::FLAG_STATIC) === Func::FLAG_STATIC) {
                    $myfunction->add_type(MyFunction::TYPE_FUNC_STATIC);
                }

                $mythisdef = new MyDefinition(0, 0, "this");
                $mythisdef->set_block_id(0);
                $mythisdef->add_type(MyDefinition::TYPE_INSTANCE);
                $myfunction->set_this_def($mythisdef);
            }
        }

        foreach ($func->params as $param) {
            $param_name = $param->name->value;
            $byref = $param->byRef;

            $mydef = new MyDefinition($param->getLine(), $param->getAttribute("startFilePos", -1), $param_name);

            if ($byref) {
                $mydef->add_type(MyDefinition::TYPE_REFERENCE);
            }

            $myfunction->add_param($mydef);

            $inst_def = new MyInstruction(Opcodes::DEFINITION);
            $inst_def->add_property(MyInstruction::DEF, $mydef);
            $this->context->get_current_mycode()->add_code($inst_def);

            unset($mydef);
        }

        // because when we call (funccall) a function by name, it can be undefined
        $this->context->get_functions()->add_function((string)$myfunction->get_name(), $myfunction);
        $this->context->set_current_func($myfunction);
    }

    public function leaveFunc(Func $func)
    {
        $mycode_codes = $this->context->get_current_mycode()->get_codes();

        $inst = $mycode_codes[count($mycode_codes) - 1];

        // we can have a block opened and we need to leave it
        if ($inst->get_opcode() !== Opcodes::LEAVE_BLOCK) {
            if (!is_null($this->context->get_current_block())) {
                $myblock = $this->s_blocks[$this->context->get_current_block()];
                $myblock->set_end_address_block(count($this->context->get_current_mycode()->get_codes()));

                $inst_block = new MyInstruction(Opcodes::LEAVE_BLOCK);
                $inst_block->add_property(MyInstruction::MYBLOCK, $myblock);
                $this->context->get_current_mycode()->add_code($inst_block);
            }
        }

        $class_name = null;
        if (!is_null($func->class)) {
            $class_name = $func->class->value;
        }

        $myfunction = $this->context->get_functions()->get_function((string)$func->name, $class_name);

        if (!is_null($myfunction)) {
            $myfunction->set_last_line($this->context->get_current_line());
            $myfunction->set_last_column($this->context->get_current_column());
            $myfunction->set_last_block_id(-1);

            $inst_func = new MyInstruction(Opcodes::LEAVE_FUNCTION);
            $inst_func->add_property(MyInstruction::MYFUNC, $myfunction);
            $this->context->get_current_mycode()->add_code($inst_func);
        }
    }

    public function parse_condition($inst_start_if, $cond)
    {
        foreach ($cond as $ops) {
            if ($ops instanceof Op\Expr\BooleanNot) {
                $inst_start_if->add_property(MyInstruction::NOT_BOOLEAN, true);
                $this->parse_condition($inst_start_if, $ops->expr->ops);
            }
        }
    }

    public function enterOp(Op $op, Block $block)
    {
        $this->context->set_current_op($op);
        $this->context->set_current_block($block);

        // for theses objects getline et getcolumn methods exists except for assertion
        if ($op instanceof Op\Stmt ||
                    ($op instanceof Op\Expr && !($op instanceof Op\Expr\Assertion)) ||
                    $op instanceof Op\Terminal) {
            if ($op->getLine() !== -1 && $op->getAttribute("startFilePos", -1) !== -1) {
                $this->context->set_current_line($op->getLine());
                $this->context->set_current_column($op->getAttribute("startFilePos", -1));
            }
        }

        if ($op instanceof Op\Stmt\JumpIf) {
            $inst_start_if = new MyInstruction(Opcodes::COND_START_IF);
            $this->context->get_current_mycode()->add_code($inst_start_if);

            $this->block_if_to_be_resolved[] = [$inst_start_if, $op->if, $op->else];

            $this->parse_condition($inst_start_if, $op->cond->ops);
        }
        /*
           const TYPE_INCLUDE = 1;
           const TYPE_INCLUDE_OPNCE = 2;
           const TYPE_REQUIRE = 3;
           const TYPE_REQUIRE_ONCE = 4;
        */

        if ($op instanceof Op\Expr\Include_) {
            if (Common::is_funccall_withoutreturn($op)) {
                // expr of type "assign" to have a defined return
                $myexpr = new MyExpr($this->context->get_current_line(), $this->context->get_current_column());
                $this->context->get_current_mycode()->add_code(new MyInstruction(Opcodes::START_EXPRESSION));

                FuncCall::instruction($this->context, $myexpr, false);

                $inst_end_expr = new MyInstruction(Opcodes::END_EXPRESSION);
                $inst_end_expr->add_property(MyInstruction::EXPR, $myexpr);
                $this->context->get_current_mycode()->add_code($inst_end_expr);
            }
        } elseif ($op instanceof Op\Terminal\GlobalVar) {
            $name_global = Common::get_name_definition($this->context->get_current_op()->var);

            $mydef_global = new MyDefinition($this->context->get_current_line(), $this->context->get_current_column(), $name_global);
            $mydef_global->set_type(MyDefinition::TYPE_GLOBAL);

            $inst_def = new MyInstruction(Opcodes::DEFINITION);
            $inst_def->add_property(MyInstruction::DEF, $mydef_global);
            $this->context->get_current_mycode()->add_code($inst_def);
        } elseif ($op instanceof Op\Terminal\Return_) {
            Assign::instruction($this->context, true);

            $inst = new MyInstruction(Opcodes::RETURN_FUNCTION);
            $inst->add_property(MyInstruction::RETURN_DEFS, $this->context->get_current_func()->get_return_defs());
            $this->context->get_current_mycode()->add_code($inst);
        } elseif ($op instanceof Op\Expr\Eval_) {
            if (Common::is_funccall_withoutreturn($op)) {
                // expr of type "assign" to have a defined return
                $myexpr = new MyExpr($this->context->get_current_line(), $this->context->get_current_column());
                $this->context->get_current_mycode()->add_code(new MyInstruction(Opcodes::START_EXPRESSION));

                FuncCall::instruction($this->context, $myexpr, false);

                $inst_end_expr = new MyInstruction(Opcodes::END_EXPRESSION);
                $inst_end_expr->add_property(MyInstruction::EXPR, $myexpr);
                $this->context->get_current_mycode()->add_code($inst_end_expr);
            }
        } elseif ($op instanceof Op\Terminal\Echo_) {
            if (Common::is_funccall_withoutreturn($op)) {
                // expr of type "assign" to have a defined return
                $myexpr = new MyExpr($this->context->get_current_line(), $this->context->get_current_column());
                $this->context->get_current_mycode()->add_code(new MyInstruction(Opcodes::START_EXPRESSION));

                FuncCall::instruction($this->context, $myexpr, false);

                $inst_end_expr = new MyInstruction(Opcodes::END_EXPRESSION);
                $inst_end_expr->add_property(MyInstruction::EXPR, $myexpr);
                $this->context->get_current_mycode()->add_code($inst_end_expr);
            }
        } elseif ($op instanceof Op\Expr\Print_) {
            if (Common::is_funccall_withoutreturn($op)) {
                // expr of type "assign" to have a defined return
                $myexpr = new MyExpr($this->context->get_current_line(), $this->context->get_current_column());
                $this->context->get_current_mycode()->add_code(new MyInstruction(Opcodes::START_EXPRESSION));

                FuncCall::instruction($this->context, $myexpr, false);

                $inst_end_expr = new MyInstruction(Opcodes::END_EXPRESSION);
                $inst_end_expr->add_property(MyInstruction::EXPR, $myexpr);
                $this->context->get_current_mycode()->add_code($inst_end_expr);
            }
        } elseif ($op instanceof Op\Expr\StaticCall) {
            if (Common::is_funccall_withoutreturn($op)) {
                // expr of type "assign" to have a defined return
                $myexpr = new MyExpr($this->context->get_current_line(), $this->context->get_current_column());
                $this->context->get_current_mycode()->add_code(new MyInstruction(Opcodes::START_EXPRESSION));

                FuncCall::instruction($this->context, $myexpr, false, false, true);

                $inst_end_expr = new MyInstruction(Opcodes::END_EXPRESSION);
                $inst_end_expr->add_property(MyInstruction::EXPR, $myexpr);
                $this->context->get_current_mycode()->add_code($inst_end_expr);
            }
        } elseif ($op instanceof Op\Expr\FuncCall || $op instanceof Op\Expr\NsFuncCall) {
            if (Common::is_funccall_withoutreturn($op)) {
                // expr of type "assign" to have a defined return
                $myexpr = new MyExpr($this->context->get_current_line(), $this->context->get_current_column());
                $this->context->get_current_mycode()->add_code(new MyInstruction(Opcodes::START_EXPRESSION));

                FuncCall::instruction($this->context, $myexpr, false);

                $inst_end_expr = new MyInstruction(Opcodes::END_EXPRESSION);
                $inst_end_expr->add_property(MyInstruction::EXPR, $myexpr);
                $this->context->get_current_mycode()->add_code($inst_end_expr);
            }
        } elseif ($op instanceof Op\Expr\MethodCall) {
            if (Common::is_funccall_withoutreturn($op)) {
                $class_name = Common::get_name_definition($op->var);

                $myexpr = new MyExpr($this->context->get_current_line(), $this->context->get_current_column());

                $this->context->get_current_mycode()->add_code(new MyInstruction(Opcodes::START_EXPRESSION));

                FuncCall::instruction($this->context, $myexpr, false, true);

                $inst_end_expr = new MyInstruction(Opcodes::END_EXPRESSION);
                $inst_end_expr->add_property(MyInstruction::EXPR, $myexpr);
                $this->context->get_current_mycode()->add_code($inst_end_expr);
            }
        } elseif ($op instanceof Op\Expr\Assign || $op instanceof Op\Expr\AssignRef) {
            Assign::instruction($this->context);
        } elseif ($op instanceof Op\Stmt\Class_) {
            $class_name = Common::get_name_definition($op);

            $myclass = new MyClass($this->context->get_current_line(), $this->context->get_current_column(), $class_name);
            $this->context->get_classes()->add_myclass($myclass);

            foreach ($op->stmts->children as $property) {
                // if($property instanceof Op\Stmt\ClassMethod)
                if ($property instanceof Op\Stmt\Property) {
                    $property_name = Common::get_name_definition($property);
                    $visibility = Common::get_type_visibility($property->visiblity);

                    $mydef = new MyDefinition($property->getLine(), $property->getAttribute("startFilePos", -1), "this");
                    $mydef->property->set_visibility($visibility);
                    $mydef->property->add_property($property_name);
                    $mydef->set_class_name($class_name);

                    // it's necessary for securityanalysis (visibility)
                    $mydef->add_type(MyDefinition::TYPE_PROPERTY);
                    $myclass->add_property($mydef);
                }
            }

            $inst_class = new MyInstruction(Opcodes::CLASSE);
            $inst_class->add_property(MyInstruction::MYCLASS, $myclass);
            $this->context->get_current_mycode()->add_code($inst_class);
        }
    }
}
