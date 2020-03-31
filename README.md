nl.sp.spbeltoolapi
=================

This extension contains CiviCRM API methods specific for the SP beltool.  

Beltool API
--------------

Adds an action to the contact API: **Contact.GetBeltoolData**.  

The options 'limit', 'offset' and 'sequential' are also supported.

| Parameter | Required | Default value | Description |
|---|---|---|---|
| contact_id | n | | Contact ID (integer) |
| group | n | | The ID of the group to which the contact should belong (or an array of group ids)
| city | n | | City (woonplaats - string or array of strings) |
| gemeente | n | | Gemeente (string or array of strings) |
| geo_code_1 | n | | Latitude (between - array of two floats) |
| geo_code_2 | n | | Longitude (between - array of two floats) |
| include_spspecial | n | 0 | Include SP staff who aren't members |
| include_memberships | n | 0 | Include SP membership data |
| include_relationships | n | 0 | Include SP relationship data |
| include_non_members | n | 0 | Include all contacts even if they are not a member |
| options.limit | n | 25 | Limit |
| options.offset | n | 0 | Offset |
| sequential | n | 0 | Sequential |
