#!/usr/bin/env python

import scraperwiki
import json
from parse import retrieve_date

def get_events():
    return scraperwiki.sql.select("* from swdata")

def event_post(event):
    dt = retrieve_date(event['datetime'])
    event['datetime'] = dt.isoformat()
    event['images'] = json.loads(event['images'])
    return event

if __name__ == '__main__':
    with open('events.json', 'wb') as out:
        json.dump([event_post(x) for x in get_events()], out)
