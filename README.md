# Cloudflare2DB
Scrapes cloudflare analytics information (bandwidth, and requests) into an InfluxDB for use with Grafana.

## Installation
1. Clone Repo
2. Copy config.json.example => config.json
3. Define your Influx database credentials & Cloudflare API settings in config.json
4. Test run `php scrape.php` in your console
5. Add `php scrape.php` to your crontab.