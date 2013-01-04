# Utility classes in PHP

## Geohash

Geohash coder/decoder in PHP.

## sequencer

`CommandChain` allows to register a sequence of `Runnable` classes, which get run in sequence when `CommandChain#start` is called.
The input of each `Runnable` is the output of the previous one, or the initial input passed to the `start` function.

## UUID

UUID generator in PHP, using as much entropy as possible.
