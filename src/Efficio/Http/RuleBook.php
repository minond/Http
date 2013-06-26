<?php

namespace Efficio\Http;

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
     * add a rule
     * @param Rule $rule
     */
    public function add(Rule $rule)
    {
        $this->rules[] = $rule;
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
