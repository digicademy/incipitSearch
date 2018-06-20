# IncipitSearch REST API
## Get Started
The IncipitSearch API is basically a elasticsearch endpoint. It is HTTP-based with request and response bodies formatted in JSON.

## API requests
### The HTTP `GET`-Request
Right now it is possible to fetch the IncipitSearch data via HTTP `GET`-Requests.
### API URL
`https://incipitsearch.adwmainz.net/json/`
### Query Parameters
* `incipit`
	* Type: string
	* The [Plaine & Easie Code](http://www.iaml.info/plaine-easie-code) to be queried or an asterisk
* `repository[]`
	* Type: string
	* A repository selection from the current repositories (GluckWV-online, RISM, SBN) 
* `page`
	* Type: Integer
	* Page number of the paginated result list
* `transposition`
	* Type: Boolean
	* `1` to search with transposition, `0` to search without
#### Example
```bash
curl -XGET "https://incipitsearch.adwmainz.net/json/?repository[]=GluckWV-online&repository[]=RISM&repository[]=SBN&transposition=1&incipit=CGCEG&page=2" -g
```
```json
{
	"took": 8,
	"timed_out": false,
	"_shards": {
		"total": 5,
		"successful": 5,
		"failed": 0
	},
	"hits": {
		"total": 314,
		"max_score": null,
		"hits": [{
			"_index": "20180525_catalog_entries",
			"_type": "catalogEntry",
			"_id": "RISM-652000216",
			"_score": null,
			"_source": {
				"catalog": "RISM",
				"catalogItemID": "652000216",
				"dataUID": 0,
				"dataURL": "https://opac.rism.info/id/rismid/652000216?format=marc",
				"detailURL": "https://opac.rism.info/search?id=652000216",
				"incipit": {
					"notes": "6'C,G'CE2(G)// //@2/4 86688CCCCE/i//",
					"clef": "G-2",
					"accidentals": "",
					"time": "3/4; 2/4",
					"completeIncipit": "%G-2$@3/4; 2/46'C,G'CE2(G)// //@2/4 86688CCCCE/i//",
					"normalizedToSingleOctave": "CGCEGCCCCE",
					"withoutOrnaments": "CGCEGCCCCE",
					"normalizedToPitch": "'C,G'C'E'G'C'C'C'C'E",
					"transposedNotes": " 7 -7 4 3 -7 0 0 0 4",
					"transposedNotesWithoutOrnaments": " 7 -7 4 3 -7 0 0 0 4"
				},
				"composer": "Anonymus",
				"title": "Abmarsch in Linie",
				"subTitle": "",
				"year": ""
			},
			"sort": ["Abmarsch in Linie"]
		}, {
		
		}]
	}
}
```