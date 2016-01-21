Web-application foundation
==========================

`Application` is a set of `Command` or `Application` items.
`Application` can be nested.

`Application` nesting is defined by base root path.
Default base root path is '/'.

When request comes, `Runner` parses it in rules of first level application (which should have base root path '/').
If `action` of underlying application is found, request handling is passed to it.
This procedure is done recursively until finished with `error` or a single `Command`.

`Runner` has a list of `Application` or `Command` 


Sample schema
=============

Commands:

`Index`
    `$action` - type of `Command` enum
        `GetToken`
        `GetData`
        
Dedicated `Application`/`ActionSet` vs unified `Option` type `COMMAND_ENUM`
Pros:
    symbolized `action` name
    
Cons:
    extra entity,
    complicated nesting,
    
    
`Runner` creates appropriate `Io` instance.
`Io` has a `Request` reader to provide properties for `Command`.
`Request` is being read against `Definition`
`Io` is being transferred to `Command` along with `Runner`.
`Runner` invokes `performAction` on a set up `Command`.
`Command` can reuse `Io` 



`Command`, `Definition`, `Io` (`Request`, `Response`)


new Io()

`Io::setupCommand` with `Request`
`Io->buildLink(CommandState)`

`Io->child()` produces new `Io`


`Command->setResponse`
`Command->perform`



Runner sets Command with a proper Io and invokes
Command can call Child command
Child command gets Response object from parent
Child command is set up with original Request (how to strip irrelevant params?)


While reading request global command state is being prepared along with global definition

