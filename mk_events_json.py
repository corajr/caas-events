#!/usr/bin/env python

import scraperwiki
import json
import calendar
from scraper import retrieve_date
from pytz import timezone

EASTERN = timezone('US/Eastern')

def get_events():
    return scraperwiki.sql.select("* from swdata")

def event_post(event):
    dt = retrieve_date(event['datetime'])    
    dt = EASTERN.localize(dt)
    event['datetime'] = calendar.timegm(dt.utctimetuple())
    event['images'] = json.loads(event['images'])
    return event

if __name__ == '__main__':
    with open('events.json', 'wb') as out:
        json.dump([event_post(x) for x in get_events()], out)
