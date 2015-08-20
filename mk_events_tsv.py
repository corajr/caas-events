#!/usr/bin/env python

import csv
import json
import calendar
import re
from datetime import datetime
from pytz import timezone
from utils import DictUnicodeWriter
from ner_people import get_names
from progressbar import ProgressBar

EASTERN = timezone('US/Eastern')

fieldnames = ['id', 'title', 'lecture-series', 'presenters', 'datetime', 'location', 'description']

def get_events():
    return json.load(open('events.json', 'r'))

def event_format(event):
    dt = datetime.fromtimestamp(event['datetime'], tz=EASTERN)
    event['datetime'] = dt.isoformat()
    match = re.search('Lecture Series|Lectures', event['title'])
    if match is not None:
        event['lecture-series'] = event['title'].replace('Lecture ', '').replace('Series', '').replace('-', '').strip()
    del event['images']
    desc = re.sub(r'\s+', ' ', event['description'])
    event['description'] = desc
    event['presenters'] = get_names(event['title'], desc)
    return event

if __name__ == '__main__':
    progress = ProgressBar()
    events = []
    for event in progress(get_events()):
        events.append(event_format(event))

    with open('events_ner.json', 'w') as jsfile:
        json.dump(events, jsfile)
    with open('events.tsv', 'w') as csvfile:
        writer = DictUnicodeWriter(csvfile, fieldnames=fieldnames,
                                dialect='excel-tab')
        writer.writeheader()
        for event in events:
            writer.writerow(event)
