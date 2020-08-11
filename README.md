##Simple test currency convertor.

###Command
Command to update and store rates from (`ecb`, `cbr` or `all`) sources.
```
php bin/console app:update:rates
```
Rates are fetching from two sources:

https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml

https://www.cbr.ru/scripts/XML_daily.asp

###API endpoints
Get all available (stored) exchange rates:
```
GET /api/v1/convertor/rates
```
Convert amount from {base} currency to {target} currency
```
GET /api/v1/convertor/{base}/{target}/{amount}
```
