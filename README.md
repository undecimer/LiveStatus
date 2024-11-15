# Live Status Plugin for YOOtheme Pro

A Joomla plugin that adds a YOOtheme Pro element to display live streaming status for various platforms.

## Supported Platforms

- TikTok
- YouTube
- Twitch

## Features

- Real-time live status detection
- Platform-specific icons
- Customizable live/offline text
- Text alignment options
- Optional icon display
- Hide when offline option
- Caching for performance
- Error handling and user feedback

## Installation

1. Download the plugin package
2. Install via Joomla Extension Manager
3. Enable the plugin in Joomla Plugin Manager
4. Clear YOOtheme Pro cache if needed

## Usage

1. Open YOOtheme Pro Page Builder
2. Add new element
3. Find "Live Status" under Basic Elements
4. Configure:
   - Select platform (TikTok/YouTube/Twitch)
   - Enter username
   - Customize text and appearance
   - Set visibility options

## Configuration

### Element Settings

- **Platform**: Choose streaming platform
- **Username**: Channel/user name (without @ symbol)
- **Live Status Text**: Text shown when live
- **Offline Status Text**: Text shown when offline
- **Text Alignment**: Left/Center/Right
- **Hide When Offline**: Toggle visibility when offline
- **Show Icon**: Toggle platform icon

### Plugin Settings

- **Cache Time**: Duration to cache status (5-3600 seconds)

## Notes

- Uses web scraping for status detection
- Cache recommended to prevent rate limiting
- Some platforms may have delays in status updates
- Requires active internet connection

## Support

For issues and feature requests, please use the GitHub repository.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
