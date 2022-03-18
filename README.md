
# Update Sophos XG(S) from Microst Endpoint Web Service

This project consumes Microsoft's Web API to get Microsoft's current endpoints and then, using Sophos XG/S's API created objects that can be used in rules in the firewall to exclude these endpoints from filtering, etc.

## Documentation

### Microsoft Endpoint Documentation

[Microsoft 365 Endpoints](https://docs.microsoft.com/en-us/microsoft-365/enterprise/microsoft-365-endpoint)

[Office 365 IP Address and URL web service](https://docs.microsoft.com/en-us/microsoft-365/enterprise/microsoft-365-ip-web-service)

### Sophos XG/S API Documentation

[API - Sophos Firewall](https://docs.sophos.com/nsg/sophos-firewall/18.5/Help/en-us/webhelp/onlinehelp/AdministratorHelp/BackupAndFirmware/API/index.html)

[How to use the API](https://docs.sophos.com/nsg/sophos-firewall/18.5/Help/en-us/webhelp/onlinehelp/AdministratorHelp/BackupAndFirmware/API/APIUsingAPI/index.html)

[API Documentation](https://docs.sophos.com/nsg/sophos-firewall/18.5/API/index.html)

## System Requirements

The folling are the requrements for the system that will run this project:

- [PHP](https://www.php.net/) 7.4.x or 8.x
    - This is written in PHP and has been tested with PHP 7.4.x and 8.x.
    - Installing PHP is beyond the scope of this document.
- [Composer](https://getcomposer.org/)
    - This system requires [ramsey/uuid](https://github.com/ramsey/uuid.git)
    - Installing Composer is beyond the scope of this document.
- Operating System
    - Windows
    - Linux

This must be run on system aside from the firewall itself. It is not supported to run this script directly on the firewall.

## Installation

1. Setup your firewall with an API users.
1. Add the device's IP address that will be running the package to the allowed API access list.
1. Grant the api user created above Read-Write access to objects.
1. Retrieve the encrypted password for your API user. This will be needed in the configuration file.
1. Setup [PHP](https://www.php.net/) for your OS.
1. Setup [Composer](https://getcomposer.org/).
1. Clone this repository.
1. From the directory where you cloned the repository to run the following commaind:
    ```
    composer install
    ```
1. Setup your config.json file.

### config.json
```
{
    "clientrequestid": "",
    "TenantName": "name",
    "NoIPv6": true,
    "localVersion": "",
    "msftURL": "https:\/\/endpoints.office.com",
    "XGURL": "firewall.example.com",
    "XGUser": "api",
    "XGPassword": "password"
}
```
#### config.json options
|   Option       | Description                                                                                                                |
|----------------|----------------------------------------------------------------------------------------------------------------------------|
|clientrequestid | This is the UUID used for identifying requests to Microsoft.                                                               |
|TenantName      | This is your tenant name for your Microsoft tenancy. This is the part before the .onmicrosoft.com                          |
|NoIPv6          | When set to true the API will exclude IPv6 addresses from the lists of IPs.                                                |
|msftURL         | This is the base URL for accessing Microsoft Web Endpoint API.                                                             |
|localVersion    | This is the last version of Microsoft's list. If this is older than the current version from Microsoft or doesn't exist, the program will rebuild the objects on the firewall.  |
|XGUser          | This us the API user created above.                                                                                        |
|XGPassword      | This is the encrypted password for the API user. See the [Sophos XG API documentation](https://docs.sophos.com/nsg/sophos-firewall/18.5/Help/en-us/webhelp/onlinehelp/AdministratorHelp/BackupAndFirmware/API/index.html#get-the-encrypted-password-for-api-requests) for how to encrypt the password.   |

## Running the program

The program can be started with the follinging command.

```
php -f main.php
```

If you manage multiple firewalls you can setup separate config.json files. 

* firewall1.json
* firewall2.json
* firewall3.json

Then run the program with the path to the config you want to be used:

```
php -f main.php firewal1.json
```

You can schedule this to run on a periodic basis using cron or Windows Task Scheduler.

The first time the program runs it will create two groups in the firewall. And IPHostGroup called "Microsoft Endpoint IPs" and a FQDNHostGroup called "Microsoft Endpoint FQDNs".

Then it will populate these groups with all the endpoints from Microsoft's list.

On subsequent runs this program will first get the current version of Microsofts endpoint list. If the version is newer than the last locally saved version it will download the latest list from Microsoft. Delete all the members of the current groups and repopulate these groups with the current endpoints from the current list.

To force the program to rebuild the obects in the firewall, delete the localVersion option from the config.json file. 
