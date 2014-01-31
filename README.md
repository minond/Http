# Routes

[![Build Status](https://travis-ci.org/minond/Http.png?branch=master)](https://travis-ci.org/minond/Http)
[![Coverage Status](https://coveralls.io/repos/minond/Http/badge.png?branch=master)](https://coveralls.io/r/minond/Http?branch=master)

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

