<?php

namespace Efficio\Http;

/**
 * siminalr to Apache's RewriteRule
 */
class Rule
{
    /**
     * rule type separator
     * ie: {groupName:type}, {id:\d+}, {zip:\d\d\d\d\d}
     */
    const TYPE_DELIM = ':';

    /**
     * convert match into dot-plus
     * ie: {page*} => /(<page>.+)/
     */
    const ANY_MATCH = '*';

    /**
     * optional group flag
     * ie: {page?} => /(<page>...)?/
     */
    const OPTIONAL_GROUP = '?';

    /**
     * matching expression
     * @var string
     */
    protected $expression;

    /**
     * human friendly expression
     * @var string
     */
    protected $template;

    /**
     * information about this rule
     * @var array
     */
    protected $info = [];

    /**
     * expression setter
     * @param string $exp
     */
    public function setExpression($exp)
    {
        $this->expression = $exp;
    }

    /**
     * expression getter
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * expression added
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        $this->expression = self::transpile($template);
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
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
     * creates a hash using all rule patterns
     * @return string
     */
    public function hash()
    {
        return md5(preg_replace('/\?P<\w+>/', '<G>', $this->expression));
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
            preg_match($this->expression, $uri, $matches);
            $match = count($matches) !== 0;
        }

        return [ $match, $matches ];
    }

    /**
     * converts a string string or a basic pattern into a regular expression.
     * escape slashes and converts groups.
     * @param string $str
     * @return string
     */
    public static function transpile($str)
    {
        $str = str_replace('/', '\/', $str);
        $str = str_replace('.', '\.', $str);
        preg_match_all('/({(.+?)})/', $str, $groups);

        if (is_array($groups) && count($groups) > 2) {
            foreach ($groups[1] as $index => $rawname) {
                $gname = $groups[2][$index];
                $type = '[A-Za-z0-9]+';
                $optional = '';

                switch (substr($gname, -1)) {
                    case self::OPTIONAL_GROUP:
                        $gname = substr($gname, 0, -1);
                        $optional = '?';
                        break;

                    case self::ANY_MATCH:
                        $gname = substr($gname, 0, -1);
                        $type = '.+';
                        break;
                }

                if (strpos($gname, self::TYPE_DELIM) !== false) {
                    list($gname, $type) = explode(self::TYPE_DELIM, $gname, 2);
                }

                $str = str_replace($rawname,
                    "(?P<{$gname}>{$type}){$optional}", $str);
            }
        }

        // add delimeters
        return '/^' . $str . '$/';
    }

    /**
     * rule factory
     * @param $expression
     * @param array $info, default: array()
     * @return Rule
     */
    public static function create($expression, array $info = [], $is_template = false)
    {
        $rule = new static;
        $rule->setInformation($info);

        if ($is_template) {
            $rule->setTemplate($expression);
        } else {
            $rule->setExpression($expression);
        }

        return $rule;
    }
}
