# Cloudflare2DB
Scrapes cloudflare analytics information (bandwidth, and requests) into an InfluxDB for use with Grafana.

## Installation
0. Install PHP and Composer
1. Clone Repo
2. Copy config.json.example => config.json
3. Define your Influx database credentials & Cloudflare API settings in config.json
4. Run `composer install`
5. Test run `php scrape.php` in your console
6. Add `php scrape.php` to your crontab.

## Usage
After testing that your cron is running, it is easy to add the data to a [Grafana instance](http://docs.grafana.org/installation/debian/). Add the [InfluxDB as a Grafana data source](http://docs.grafana.org/features/datasources/influxdb/), and the cloudflare stats will now be visible in your dashboard editor.

![Example](https://i.imgur.com/JrlokZV.png)
