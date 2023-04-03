# Sitegeist.OffCORS 
## CORS Middleware for Neos-CMS / Flow-Framework

Very basic (for now) CORS implementation for Neos CMS

### Authors & Sponsors

* Martin Ficzel - ficzel@sitegeist.de
* Melanie Wüst - wuest@sitegeist.de

*The development and the public-releases of this package is generously sponsored by our employer https://www.sitegeist.de.*

## Configuration

```yaml
Sitegeist:
  OffCORS:
    # allow credentials via cors (bool)
    allowCredentials: false
    # allowed cors origins (array or csv-string)
    allowOrigins: "*"
    # allowed cors methods (array or csv-string)
    allowMethods: "GET"
    # allowed cors headers (array or csv-string)
    allowHeaders: ""
    # exposed cors headers (array or csv-string)
    exposeHeaders: ""
    # max age cors informations (int)
    maxAge: 86400
```

## Installation

Sitegeist.OffCORS is available via packagist. Just run `composer require sitegeist/offcors` to install it. We use semantic-versioning so every breaking change will increase the major-version number.

## Contribution

We will gladly accept contributions. Please send us pull requests.
