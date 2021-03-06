# Change Log

## Unreleased

### Fixed

- Bug in docs and added in some missing docs #32

## 0.4.1 - 2017-06-27

### Added

- Configurable Jobs #30

## 0.4.0 - 2017-06-26

### Added

- Synchronous Queue Support #11
- Doctrine Queue Support #12
- WrappedJob Helper methods
- Job Status Command #23
- Multiple Queue Providers #21
- Batched Workers #22
- Documentation on config options
- Custom logging #15

## 0.3.4 - 2017-06-03

### Added

- Support for php7 throwables in catching exceptions

### Fixed

- Bug in consume where exceptions weren't being caught

## 0.3.3 - 2017-06-02

### Added

- Kernel Dispatch #13
- Job Restart #14
    - New commands: `job:restart` `job:stop` and `job:reset`
    - Cache integration
- Respawn Option #17

## 0.3.2 - 2017-03-30

### Added

- New Sqs Queue Provider
- New `delay` option in the WrappedJobBuilder to support delayed queueing.
- Alternative options for choosing your queue.

### Fixed

- Bug in consumer startup.
- Bug in Redis queue that would prevent modified jobs from being removed from processing queue.

## 0.3.1 - 2017-03-17

- Fixed bug in Kernel

## 0.3.0 - 2017-03-16
### Added

- Tight Cargo Integration, removed pimple integration
- Kernel implements Dispatch interface
- Simple `complete` and `failed` methods to return specific job results
- New FailJob stacks to properly handle the failing of jobs.
- Consume file locking to allow for scheduled invoking of the `job:consumer` command.
- New default Stub QueueManager

### Changed

- Updated ttl behavior to not actually kill the loop until the queue is empty.
- Updated dependencies
- Removed `Queue::fail` as to move that logic to the FailJob module.

## 0.2.0 - 2017-01-19
### Added

- Streamlined setup process for jobs
- Tight Pimple integration
- Created a simple Dispatch Interface
- Added new Kernel to manage everything
- Documentation
- This CHANGELOG
- YAML configuration
- Added a new Command named ConsumeCommand which is the only entry point to
  start the jobs.
- ScheduleLoop system to allow full customization of how the scheduling works
- Simple `complete` and `failed` methods to return specific job results
- Added AutoArgs for Job Handlers

### Changed

- Simplified the Scheduler in favor of ScheduleLoop
- Refactored a lot of the old producer/consumer stuff
- Refactored the Job class to be a simple interface where jobs hold the data
  and handler code.
- Hid the Scheduler and Worker command in favor of Consume Command.

## 0.1.1 - 2016-01-05
### Changed

Bumping `krak\mw` library dependency to latest at ^0.3.0

## 0.1.0 - 2016-12-02
### Added

- Initial Implementation
- Redis Queue
- Auto Scheduling
