Entities
========

`Project` used for localizing application scope.

`Stat` is a record of profile values.

`Run` is a single or combined set of profile data, belongs to a single `Project`.
`Run` contains `Stat` record as total values.

`Tag` is a text description of `Run` context. One `Run` can have multiple `Tag`. 
A combined `Run` is anchored by set of `Tag`.

`RunTag` holds `Tag` marks for `Run` items.

`Symbol` is name of function appeared in profile data.

`SymbolStat` is a `Stat` record of a single `Symbol` during `Run`. 
It can be inclusive or exclusive.

`RelatedStat` is a `Stat` record of a parent-child direct relation of two `Symbol` during `Run`.
