# Routes

[![Build Status](https://travis-ci.org/minond/Http.png?branch=master)](https://travis-ci.org/minond/Http)
[![Coverage Status](https://coveralls.io/repos/minond/Http/badge.png?branch=master)](https://coveralls.io/r/minond/Http?branch=master)
[![Latest Stable Version](https://poser.pugx.org/minond/http/version.png)](https://packagist.org/packages/minond/http)
[![Dependencies Status](https://depending.in/minond/Http.png)](http://depending.in/minond/Http)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/e3ecc490-f8e2-479c-aa5b-d84c4904bd09/mini.png)](https://insight.sensiolabs.com/projects/e3ecc490-f8e2-479c-aa5b-d84c4904bd09)

## Keys

Shared keys:
* format - defaults to html
* method - http method. can also be placed before the url

Controller/action keys:
* namspaces - defaults to app:namespace
* controller
* action

Static resource keys:
* base - base directory
* file - file name without extension

## Samples

```yaml
# controller/action:
/tasks/index:
  controller: Tasks
  action: index

POST /tasks/create:
  controller: Tasks
  action: create

/tasks/update:
  controller: Tasks
  action: update
  method: POST

# static resource:
/public/js/{file}.{format}:
    base: public/vendor/javascript
```

