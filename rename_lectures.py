import json
import re
from collections import defaultdict
from pprint import pprint

def get_events():
    events = json.load(open('events_ner.json', 'r'))
    return {v['id']:v for v in events}


def strip_quotes(event):
    event['title'] = event['title'].strip('" ')
    return event
    
def find_repeats(events):
    seen = defaultdict(list)
    for event in events:
        seen[event['title']].append(event['id'])
    return {k:v for k,v in seen.iteritems() if len(v) > 1}

split_pat = r"Lecturer|Speaker|This|[[-]"

if __name__ == '__main__':
    events = get_events()
    repeated_titles = find_repeats(events.values())
    for title, repeats in repeated_titles.iteritems():
        for key in repeats:
            event = events[key]
            groups = re.split(split_pat, event['description'])
            if groups is not None:
                if event.get('lecture-series') is None:
                    event['lecture-series'] = title
                maybe_title = groups[0].strip()
                if len(maybe_title) > 0:
                    event['title'] = maybe_title.replace('Featuring ', '')
            events[key] = event
    final_events = [strip_quotes(event) for event in events.values()]
    with open('events_final.json','wb') as out:
        json.dump(final_events, out)
