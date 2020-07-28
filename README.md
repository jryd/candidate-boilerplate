## Weather forecast app

### Installation 

Create `.env`

```
cp .env.example .env
```

Add Open weather map api key

```
OPENWEATHERMAP_API_KEY=<API_KEY>
```

Create env.js

```
cd resources/js
cp env-example.js env.js
```

Run

```
php artisan serve
```

### Fronend

Select a city to view a forecast. 

The list contains an invalid city to demonstrate error handling.

### Report

Print a forecast report for one or more cities
 
```
> php artisan forecast:city --cities=Brisbane
Forecast for Brisbane
+----------+--------+---------+-----------+----------+--------+
| Brisbane | Monday | Tuesday | Wednesday | Thursday | Friday |
+----------+--------+---------+-----------+----------+--------+
| Temp Min | 21.35  | 19.49   | 20.09     | 20.77    | 21.84  |
| Temp Max | 40.51  | 28.77   | 27.86     | 28.9     | 33.46  |
| Humidity | 65     | 70      | 66        | 71       | 70     |
| Weather  | Clouds | Clouds  | Clouds    | Clouds   | Clear  |
+----------+--------+---------+-----------+----------+--------+
```
