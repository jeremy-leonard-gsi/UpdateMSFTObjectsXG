
# Update Sophos XG(S) from Microst Endpoint Web Service

This project consumes Microsoft's Web API to get Microsoft's current endpoints and then, using Sophos XG/S's API created objects that can be used in rules in the firewall to exclude these endpoints from filtering, etc.

## System Requirements

The folling are the requrements for the system that will run this project:

- [PHP](https://www.php.net/) 7.4.x or 8.x
    - This is written in PHP and has been tested with PHP 7.4.x and 8.x.
- [Composer](https://getcomposer.org/)
    - This system requires [ramsey/uuid](https://github.com/ramsey/uuid.git)
- Operating System
    - Windows
    - Linux

This must be run on another system aside from the firewall itself. It is not supported to run this script directly on the firewall.

## Installation

1. Setup [PHP](https://www.php.net/) for your OS
2. Setup [Composer](https://getcomposer.org/)
3. Clone this repository
4. From the directory where you cloned the repository to run the following commaind:
    ```
    composer install
    ```
5. Setup your config.json file

## config.json
```
{
    "clientrequestid": "",
    "XGURL": "firewall.example.com",
    "XGUser": "api",
    "XGPassword": "password",
    "TenantName": "name",
    "NoIPv6": true,
    "msftURL": "https:\/\/endpoints.office.com"
}
```
