Entities
========

`Project` used for localizing application scope.

`Stat` is a record of profile values.

`Run` is a single or combined set of profile data, belongs to a single `Project`.
`Run` contains `Stat` record as total values. 
Multiple `Run` can be merged in new one. 
`Run` can be updated with a set of additional `Stat`.


`Symbol` is name of function appeared in profile data.

`SymbolStat` is a `Stat` record of a single `Symbol` during `Run`. 
It can be inclusive or exclusive. Optionally can be reduced to contain only essential records. 

`RelatedStat` is a `Stat` record of a parent-child direct relation of two `Symbol` during `Run`.


Continuous Profiling
====================

Facility for saving stats grouped by time and tags.

`Aggregate` is a pointer to combined run which contains data for a specific time interval. 
It can be `minutely`, `hourly`, `daily`, `weekly`, `monthly`.
Reduced by `Project`.
Reduced by `TagGroup`.

`Aggregate` can be free of `RelatedStat` data. `SymbolStat` can be reduced for some historical `Aggregate`s.
`Aggregate` can be closed or opened for modifications. 

`Tag` is a text description of `Aggregate` context. One `Aggregate` 
can have multiple `Tag`.
`Tag` can contain: server name, php version, application name, 
controller info, code version, etc.

`TagGroup` is a unique set of `Tag` in `Project`.

For a single `Tag` multiple `TagGroup` can be found, 
and multiple items of `Aggregate` can be combined to one temporary `ReportAggregate` 
with a `Run` containing combined data.

`ReportAggregate` items are deleted after expiration. All associated data is being deleted too.
`ReportAggregate` on opened `Aggregate` items is frozen by time of creation but can be refreshed.

TODO: add percentiles for aggregated stats.
