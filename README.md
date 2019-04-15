# zipdevtest

API resources:
/api/resources/create.php
/api/resources/read.php
/api/resources/update.php
/api/resources/delete.php

to create a contact (phone book contact) the POST should have
first_name
last_name
extra (was thinking on add the picture of the contact, but was not implemented)

To add emails should be an array called emails with the following structure:
emails[0][name] <= email name (as example personal, professional, company email, etc).
emails[0][email] <= the email itself.

To add phones to a contact should be an array called phones with the following structure:
phones[0][name] <= phones name (as example home, mobile, company number, etc).
phones[0][phone] <= the number itself.

the resource read.php displays all the contact in the phonebook

to update a user should use /api/resources/update.php (PUT)
must have the id value of the phonebook contact (required).
The rest is optional.

to update, create another or delete emails or phones to a phonebook contact, the structure must be as follows:
emails[0][action] <= create/update/delete
phones[0][action] <= create/update/delete

if create or delete a an email or phone 
must have the ID of the email/phone to be modified as follows
emails[0][id] <= id of the email
phones[0][id] <= id of the phone

to update an email/phone should have at least one field:

phones[0][name] <= phones name (as example home, mobile, company number, etc).
phones[0][phone] <= the number itself.
or 
emails[0][name] <= email name (as example personal, professional, company email, etc).
emails[0][email] <= the email itself.

