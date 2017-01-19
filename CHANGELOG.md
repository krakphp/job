# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
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

### Changed

- Refactored a lot of the old producer/consumer stuff
- Refactored the Job class to be a simple interface
- Hid the Scheduler and Worker command in favor of Consume Command.

## [0.1.1] - 2016-01-05
### Changed

Bumping `krak\mw` library dependency to latest at ^0.3.0

## [0.1.0] - 2016-12-02
### Added

- Initial Implementation
- Redis Queue
- Auto Scheduling