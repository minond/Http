<?php

namespace Efficio\Http;

/**
 * siminalr to Apache's RewriteRule
 */
class Rule
{
    /**
     * matching expressions
     * @var string[]
     */
    protected $expressions = [];

    /**
     * information about this rule
     * @var array
     */
    protected $info = [];

    /**
     * all rules
     * @var Rule[]
     */
    protected static $pool;

    /**
     * adds self to rule pool
     */
    final public function __construct()
    {
        self::$pool[] = $this;
    }

    /**
     * expression added
     * @param string $exp
     */
    public function addExpression($exp)
    {
        $this->expressions[] = $exp;
    }

    /**
     * @param array $info
     */
    public function setInformation(array $info)
    {
        $this->info = $info;
    }

    /**
     * @return array
     */
    public function getInformation()
    {
        return $this->info;
    }

    /**
     * checks if an expression matches string. returns array with following data:
     *  1) match, boolean
     *  2) matches, array
     *  3) expression, string
     *
     * @param string $str
     * @return array[boolean, array, string]
     */
    public function matches($str)
    {
        $match = false;
        $matches = [];
        $exp = '';

        foreach ($this->expressions as $exp) {
            preg_match($exp, $str, $matches);

            if (count($matches)) {
                $match = true;
                break;
            }
        }

        return [ $match, $matches, $exp ];
    }

    /**
     * converts a string string or a basic pattern into a regular expression
     * @param string $str
     * @return string
     */
    public static function transpile($str)
    {
        $str = str_replace('/', '\/?', $str);

        // convert groups
        preg_match_all('/({(.+?)})/', $str, $groups);

        if (is_array($groups) && count($groups)) {
            if (isset($groups[1]) && isset($groups[2])) {
                foreach ($groups[1] as $index => $rawname) {
                    $gname = $groups[2][$index];
                    $op = '';

                    if (substr($gname, -1) === '?') {
                        $gname = substr($gname, 0, -1);
                        $op = '?';
                    }

                    $str = str_replace($rawname,
                        "(?P<{$gname}>[A-Za-z0-9]+){$op}", $str);
                }
            }
        }

        // add delimeters
        return '/' . $str . '/';
    }

    /**
     * rule factory
     * @param array $expressions
     * @param array $info, default: array()
     * @return Rule
     */
    public static function create(array $expressions, array $info = array())
    {
        $rule = new static;
        $rule->setInformation($info);

        foreach ($expressions as $exp) {
            $rule->addExpression($exp);
        }

        return $rule;
    }

    /**
     * find a matching rule and return its information merged with the matches
     * @param string $str
     * @return array
     */
    public static function matching($str)
    {
        $matching = null;

        foreach (self::$pool as & $rule) {
            list($ok, $matches) = $rule->matches($str);

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
