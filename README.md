nl.sp.spbeltoolapi
=================

This extension contains CiviCRM API methods specific for the SP beltool.  

Beltool API
--------------

Adds an action to the contact API: **Contact.GetBeltoolData**.  

The options 'limit', 'offset' and 'sequential' are also supported.

| Parameter | Required | Default value | Description |
|---|---|---|---|
| group | y | | The ID of the group to which the contact should belong (or an array of group ids)
| contact_id | n | | Contact ID (integer) |
| options.limit | n | 25 | Limit |
| options.offset | n | 0 | Offset |
| group_contact_id_offset | n | 0 | Offset of group contact id, used as last imported contact id |
| get_count | n | 0 | Count contacts only |

