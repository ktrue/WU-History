This folder will be used to store the JSON returns from api.weather.com
for the MONTHLY data retrieved by WXDailyHistory.php shim program.

The files are named:

wuYYYYMM-<WUID>-<WUTYPE>.json

where:
  YYYY = year
  MM   = 2-digit month
  WUID = WeatherUnderground Station ID (like KCASARAT1)
  WUTYPE= format of data:
          'e' = Imperial (F,mph,inHg,in)
          'm' = Metric   (C,km/h,hPa,mm)
          's' = SI       (C,m/s,hPa,mm)
          'h' = UK       (C,mph,mb,mm)

The cache files are filled as needed and serve to speed up the data retrieval

NOTE:  as of 23-May-2019, the earliest year available via the API is 2008

This file serves as a placeholder to ensure creation of a cache/ directory.
