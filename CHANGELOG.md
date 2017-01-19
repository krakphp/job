# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## [0.2.0] - 2017-01-19
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

## [0.1.1] - 2016-01-05
### Changed

Bumping `krak\mw` library dependency to latest at ^0.3.0

## [0.1.0] - 2016-12-02
### Added

- Initial Implementation
- Redis Queue
- Auto Scheduling
