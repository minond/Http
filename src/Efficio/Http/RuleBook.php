<?php

namespace Efficio\Http;

use Efficio\Http\Request;
use Efficio\Http\Error\DuplicateRuleException;

/**
 * a collection of Rules
 */
class RuleBook
{
    /**
     * @var Rule[]
     */
    private $rules = [];

    /**
     * rule hashes
     * @var string[]
     */
    private $rhash = [];

    /**
     * add a rule
     * @param Rule $rule
     * @throws DuplicateRuleException
     */
    public function add(Rule $rule)
    {
        $hash = $rule->hash();

        if (!in_array($hash, $this->rhash)) {
            $this->rhash[] = $hash;
            $this->rules[] = $rule;
        } else {
            throw DuplicateRuleException::create($rule);
        }
    }

    /**
     * add multiple rules
     * @param array|object $rules
     * @param is_template, default: false
     * @throws DuplicateRuleException
     * @throws InvalidArgumentException
     */
    public function load($rules, $is_template = false)
    {
        if (is_object($rules) || is_array($rules)) {
            foreach ($rules as $route => $info) {
                $rule = Rule::create($route, $info, $is_template);
                $this->add($rule);
            }
        } else {
            throw new \InvalidArgumentException(
                'First argument must be an array or an object');
        }
    }

    /**
     * get all rules in this rule book
     * @return Rule[]
     */
    public function all()
    {
        return $this->rules;
    }

    /**
     * find a matching rule and return its information merged with the matches
     * @param Request|string $req
     * @param boolean $merge, merge the rule's information into the request on match
     * @return array
     */
    public function matching($req, $merge = false)
    {
        $matching = null;
        $matches = [];

        foreach ($this->rules as & $rule) {
            list($ok, $matches) = $rule->matches($req);

            if ($ok) {
                $matching = $rule;

                if ($req instanceof Request) {
                    $req->setRule($rule);
                }

                unset($rule);
                break;
            }

            unset($rule);
        }

        if ($matching) {
            $stringinfo = [];
            $ruleinfo = $matching->getInformation();

            foreach ($matches as $key => $val) {
                if (is_string($key)) {
                    $stringinfo[ $key ] = $val;
                }
            }

            $matching = array_merge($ruleinfo, $stringinfo);

            if ($merge) {
                foreach ($matching as $param => $value) {
                    $req->param->{ $param } = $value;
                }
            }
        }

        return $matching;
    }
}
