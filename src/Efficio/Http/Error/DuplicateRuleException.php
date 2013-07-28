<?php

namespace Efficio\Http\Error;

use Exception;
use Efficio\Http\Rule;

/**
 * duplicate rule exception
 * @used-by RuleBook
 */
class DuplicateRuleException extends Exception
{
    /**
     * @var Rule
     */
    private $rule;

    /**
     * @param Rule $rule
     */
    public function setRule(Rule $rule)
    {
        $this->rule = $rule;
    }

    /**
     * @return Rule
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * @param Rule $rule
     * @return DuplicateRuleException
     */
    public static function create(Rule $rule)
    {
        $ex = new static;
        $ex->setRule($rule);
        return $ex;
    }
}
