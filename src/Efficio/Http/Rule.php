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
     * @param Request|string $req
     * @return array[boolean, array, string]
     */
    public function matches($req)
    {
        $match = false;
        $matches = [];
        $exp = '';
        $uri = $req;
        $method = false;
        $runcheck = true;

        if ($req instanceof Request) {
            $uri = $req->getUri();

            // quick method check
            if (
                isset($this->info['method']) &&
                $this->info['method'] !== $req->getMethod()
            ) {
                $runcheck = false;
            }
        }

        if ($runcheck) {
            foreach ($this->expressions as $exp) {
                preg_match($exp, $uri, $matches);

                if (count($matches)) {
                    $match = true;
                    break;
                }
            }
        }

        return [ $match, $matches, $exp ];
    }

    /**
     * converts a string string or a basic pattern into a regular expression.
     * escape slashes and converts groups.
     * @param string $str
     * @return string
     */
    public static function transpile($str)
    {
        $str = str_replace('/', '\/?', $str);
        $str = str_replace('.', '\.?', $str);
        preg_match_all('/({(.+?)})/', $str, $groups);

        if (is_array($groups) && count($groups) > 2) {
            foreach ($groups[1] as $index => $rawname) {
                $gname = $groups[2][$index];
                $op = '';

                // $type and $mult are extracted out of string in case of
                // future enhancements to manipulate these in any way.
                $type = '[A-Za-z0-9]';
                $mult = '+';

                switch (substr($gname, -1)) {
                    case '?':
                        $gname = substr($gname, 0, -1);
                        $op = '?';
                        break;

                    case '*':
                        $gname = substr($gname, 0, -1);
                        $type = '.';
                        break;
                }

                $str = str_replace($rawname,
                    "(?P<{$gname}>{$type}{$mult}){$op}", $str);
            }
        }

        // add delimeters
        return '/^' . $str . '$/';
    }

    /**
     * rule factory
     * @param array $expressions
     * @param array $info, default: array()
     * @return Rule
     */
    public static function create(array $expressions, array $info = [])
    {
        $rule = new static;
        $rule->setInformation($info);

        foreach ($expressions as $exp) {
            $rule->addExpression($exp);
        }

        return $rule;
    }
}
