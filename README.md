# LaravelVueCodingAssignment 
## $\textcolor{red}{\textsf{Important info}}$
In order to make this project work you should first edit "config/zoho_crm_data.php" file.

There are 4 fields to be set: "access_token", "refresh_token", "client_id", "client_secret".
These are your personal Zoho CRM API authentication credentials. You can get them following 
the official tutorial at [www.zoho.com/crm/developer/docs/api/v2/oauth-overview](https://www.zoho.com/crm/developer/docs/api/v2/oauth-overview.html)

These values work like seeders for populating the database tables with encrypted values, so you can remove them from "config/zoho_crm_data.php" file.

But before you remove them, launch the poputization process, open terminal in the project root folder and run "php artisan server:start-setup". That's it.
