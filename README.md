# Speed Optimizer
WordPress speed optimization plugin with PageSpeed Insights integration and personalized configuration options.

## Features

### PageSpeed Insights Integration
- Run speed tests directly from WordPress admin
- Get detailed performance metrics for both desktop and mobile
- Track historical performance data
- View optimization recommendations

### Personalized Configuration
- **Caching**: Browser caching with customizable expiration times
- **Minification**: CSS and JavaScript file minification
- **Compression**: GZIP compression for reduced file sizes
- **Image Optimization**: Lazy loading for images
- **Database Optimization**: Clean spam, revisions, and optimize tables
- **CDN Support**: Content Delivery Network integration
- **Optimization Levels**: None, Basic, Moderate, Aggressive

### Admin Interface
- **Dashboard**: Overview of performance metrics and quick actions
- **Speed Test**: Run comprehensive PageSpeed Insights tests
- **Settings**: Personalized configuration options
- **Import/Export**: Backup and restore plugin settings

## Installation

1. Upload the plugin files to `/wp-content/plugins/speed/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Speed Optimizer' in the admin menu to configure settings

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- PageSpeed Insights API key (free from Google)

## Getting Started

1. **Get API Key**: Visit [Google Developers Console](https://developers.google.com/speed/docs/insights/v5/get-started) to get your free PageSpeed Insights API key
2. **Configure Settings**: Go to Speed Optimizer > Settings and enter your API key
3. **Run First Test**: Use the Speed Test page to analyze your website
4. **Enable Optimizations**: Configure optimization settings based on your needs
5. **Test Premium Features**: Use the test license keys provided in [TEST-LICENSE-KEYS.md](TEST-LICENSE-KEYS.md) to test premium functionality

## Configuration Options

### General Settings
- **Enable Caching**: Browser caching headers
- **Cache Expiration**: How long browsers cache resources
- **GZIP Compression**: Compress pages for faster delivery

### Optimization Settings
- **Optimization Level**: Choose between None, Basic, Moderate, Aggressive
- **Minification**: Reduce CSS/JS file sizes
- **Image Optimization**: Lazy loading implementation
- **Database Optimization**: Automatic cleanup and optimization

### Advanced Settings
- **CDN URL**: Content Delivery Network integration
- **Exclude Files**: Files to skip during optimization
- **Cache Management**: Clear cache functionality

## Usage

### Running Speed Tests
1. Go to Speed Optimizer > Speed Test
2. Enter the URL to test (defaults to your homepage)
3. Choose strategy (Desktop, Mobile, or Both)
4. Click "Run Speed Test"
5. View detailed results including scores and recommendations

### Optimization
1. Go to Speed Optimizer > Settings
2. Configure optimization options based on your needs
3. Save settings to apply optimizations
4. Use Quick Actions on dashboard for cache clearing and database optimization

### Monitoring
- Dashboard shows overview of recent tests and performance metrics
- Activity log tracks all optimization actions
- Historical data helps track improvements over time

## Troubleshooting

### API Key Issues
- Ensure your API key is valid and active
- Check that PageSpeed Insights API is enabled in Google Console
- Test the API key using the "Test API Key" button in settings

### Performance Issues
- Start with "Basic" optimization level
- Exclude problematic files in Advanced settings
- Clear cache after making changes

### PageSpeed Test Timeouts
- If you see "RPC::DEADLINE_EXCEEDED: context deadline exceeded" errors, this usually indicates the website being tested is very slow or Google's servers are busy
- The plugin now automatically retries failed tests with exponential backoff
- Tests have increased timeouts (up to 3 minutes) to handle slow websites
- If tests consistently fail, try testing during off-peak hours or check if the website URL is accessible

### Compatibility
- Test optimizations on staging environment first
- Some themes/plugins may conflict with aggressive optimization
- Use exclude files option for compatibility

## Testing Premium Features

The plugin includes a demo/development mode that allows you to test premium features using test license keys. See [TEST-LICENSE-KEYS.md](TEST-LICENSE-KEYS.md) for available test license keys and instructions.

### Available Test Tiers
- **Premium**: Advanced caching, Critical CSS, WebP conversion
- **Business**: Multisite support, Scheduled optimization  
- **Agency**: White-labeling, Client management

To test premium features:
1. Go to Speed Optimizer → License in your WordPress admin
2. Use one of the test license keys from [TEST-LICENSE-KEYS.md](TEST-LICENSE-KEYS.md)
3. Activate the license to unlock premium features

## Support

For support, please visit the plugin repository or contact the developer.

## License

This plugin is licensed under GPL v3 or later.
