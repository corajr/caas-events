#!/usr/bin/env python

import scraperwiki
import os
import urlparse, urllib
from bs4 import BeautifulSoup
import re
import json
from datetime import datetime
# import urllib.parse as urlparse, urllib.request as urllib

def path2url(path):
    path = os.path.abspath(path)
    return urlparse.urljoin('file:', urllib.pathname2url(path))

base_url = path2url("./www.princeton.edu/africanamericanstudies/events_archive/index.xml")
archive_url = urlparse.urljoin(base_url, "index.xml")

date_re = re.compile(r"(\d+)/(\d+)/(\d+)\s+at\s+(\d+):(\d\d) (.m)")

def scrape_all_years(url, dry_run=False):
    soup = BeautifulSoup(scraperwiki.scrape(url))
    year_p = soup.find('strong', text="View year:")
    years = [a['href'] for a in year_p.find_next_siblings("a")]
    if dry_run:
        return years
    else:
        for year in years:
            scrape_index(urlparse.urljoin(base_url, year))

def scrape_index(url, dry_run=False):
    soup = BeautifulSoup(scraperwiki.scrape(url))
    events = soup.select("li.category1 a")

    if dry_run:
        return [a['href'] for a in events]
    else:
        for event in events:
            scrape_event(urlparse.urljoin(base_url, event['href']))

def mk_clean_date(date_str):
    parts = [x for x in date_re.match(date_str).groups()]
    parts[3] = parts[3].zfill(2)
    parts[5] = parts[5].upper()
    new_str = ' '.join(parts)
    return datetime.strptime(new_str, "%m %d %y %I %M %p")


def retrieve_date(dt_str):
    return datetime.strptime(dt_str, "%Y-%m-%d %H:%M:%S.%f")


def scrape_event(url):
    soup = BeautifulSoup(scraperwiki.scrape(url))
    event_id = int(urlparse.parse_qs(urlparse.urlparse(url).query)['id'][0])
    data = {'id': event_id}
    data['title'] = soup.find("h4").get_text(strip=True)
    data['location'] = soup.select(".events_location > strong")[0].next_sibling.strip()
    date_str = soup.select(".events_datetime > strong")[0].next_sibling.split('-')[0]
    data['datetime'] = mk_clean_date(date_str)
    data['description'] = '\n\n'.join(soup.select('.events_desc')[0].stripped_strings)
    data['images'] = json.dumps([img['src'] for img in soup.select('p img')])
    
    scraperwiki.sql.save(["id"], data)

if __name__ == '__main__':
    scrape_all_years(archive_url)
