<?php

namespace Efficio\Http;

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
     * @throws DuplicateRuleException
     * @throws InvalidArgumentException
     */
    public function load($rules)
    {
        if (is_object($rules) || is_array($rules)) {
            foreach ($rules as $route => $info) {
                $path = Rule::transpile($route);
                $rule = Rule::create([ $path ], $info);
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
     * @return array
     */
    public function matching($req)
    {
        $matching = null;

        foreach ($this->rules as & $rule) {
            list($ok, $matches) = $rule->matches($req);

            if ($ok) {
                $matching = $rule;
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
        }

        return $matching;
    }
}
