# Smart Irrigation System

An automated irrigation system using ESP32 microcontroller and web interface for monitoring and control.

## Hardware Requirements

- ESP32 Development Board
- Soil Moisture Sensor
- Water Pump/Relay Module
- LED (for status indication)
- Jumper Wires
- Power Supply

## Pin Connections

| Component | ESP32 Pin |
|-----------|-----------|
| Soil Moisture Sensor (Analog) | GPIO34 (VP) |
| Water Pump Relay | GPIO4 |
| Status LED | GPIO5 |

## Software Requirements

- XAMPP (Apache + MySQL)
- Arduino IDE
- Required Libraries:
  - WiFi
  - HTTPClient
  - ArduinoJson

## Installation

1. Database Setup:
   - Import `Schema.sql` into MySQL using phpMyAdmin
   - Configure database credentials in `api/db_config.php`

2. Web Interface:
   - Copy all files to XAMPP htdocs directory
   - Start Apache and MySQL services

3. ESP32 Setup:
   - Open `esp32_code/smart_irrigation.ino` in Arduino IDE
   - Update WiFi credentials and server URL
   - Install required libraries
   - Upload code to ESP32

## Features

- Real-time moisture monitoring
- Automatic/Manual pump control
- Irrigation scheduling
- Historical data tracking
- User authentication
- Mobile-responsive interface

## API Endpoints

- `/api/update_sensor_data.php` - Handle sensor data
- `/api/water_control.php` - Control water pump
- `/api/get_schedules.php` - Manage irrigation schedules
- `/api/auth/*` - Authentication endpoints

## Usage

1. Access web interface at `http://[your-ip]/abhi-mp/`
2. Register/Login to access controls
3. Monitor soil moisture levels
4. Set up irrigation schedules
5. Manual override available for pump control

## Calibration

For accurate moisture readings:
1. Place sensor in dry air - note the value (AIR_VALUE)
2. Place sensor in water - note the value (WATER_VALUE)
3. Update these values in ESP32 code
4. Default thresholds:
   - Below 20% - Very dry (pump on)
   - Above 60% - Sufficiently moist (pump off)

## Troubleshooting

1. Connection Issues:
   - Verify WiFi credentials
   - Check server URL
   - Confirm Apache/MySQL running

2. Sensor Readings:
   - Check pin connections
   - Verify power supply
   - Clean sensor probes
   - Recalibrate if needed

3. Pump Control:
   - Check relay connections
   - Verify pump power supply
   - Test manual control first

## Security Notes

- Change default database credentials
- Use strong passwords
- Keep system updated
- Monitor access logs

## License

MIT License - Feel free to use and modify as needed.
