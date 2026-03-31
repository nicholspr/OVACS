# OVACS - PHP Landing Page

A modern, responsive landing page built with PHP, HTML5, CSS3, and JavaScript.

## Features

- **Responsive Design**: Optimized for desktop, tablet, and mobile devices
- **Modern UI**: Clean, professional design with smooth animations
- **Contact Form**: Functional PHP contact form with validation
- **SEO Friendly**: Semantic HTML structure and meta tags
- **Fast Loading**: Optimized CSS and JavaScript
- **Cross-browser Compatible**: Works on all modern browsers

## Project Structure

```
OVACS/
├── index.php          # Main landing page
├── css/
│   └── style.css      # Stylesheet
├── js/
│   └── main.js        # JavaScript functionality
├── images/            # Image assets (placeholder)
├── includes/
│   ├── header.php     # Header navigation
│   └── footer.php     # Footer content
├── .gitignore         # Git ignore file
└── README.md          # Project documentation
```

## Sections

1. **Hero Section**: Eye-catching introduction with call-to-action buttons
2. **About Section**: Company information and statistics
3. **Features Section**: Key benefits and services
4. **Contact Section**: Contact information and functional contact form

## Technologies Used

- **Backend**: PHP 7.4+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Fonts**: Google Fonts (Inter)
- **Responsive**: CSS Grid and Flexbox

## Installation & Setup

1. **Clone or download** this repository
2. **Place files** in your web server directory:
   - For XAMPP: `C:\xampp\htdocs\OVACS\`
   - For IIS: `C:\inetpub\wwwroot\OVACS\`
3. **Access** via your browser: `http://localhost/OVACS/`

## Deployment Scripts

You can create deployment scripts for easy updates:

### For IIS:
```batch
robocopy "C:\DATA\GIT\phpOVACS" "C:\inetpub\wwwroot\OVACS" *.php *.css *.html *.js /s /e /xd .git .vscode
```

### For XAMPP:
```batch
robocopy "C:\DATA\GIT\phpOVACS" "C:\xampp\htdocs\OVACS" *.php *.css *.html *.js /s /e /xd .git .vscode
```

## Contact Form

The contact form includes:
- **Client-side validation** with JavaScript
- **Server-side validation** with PHP
- **Email validation** and sanitization
- **Success/error messages**

## Customization

### Colors
Main brand colors can be changed in `css/style.css`:
- Primary: `#2563eb` (Blue)
- Secondary: `#fbbf24` (Yellow)
- Dark: `#1f2937` (Dark Gray)

### Content
Update content in `index.php`:
- Company name and description
- Statistics and numbers
- Contact information

## Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## Author

Created with ❤️ for OVACS

## License

This project is open source and available under the [MIT License](LICENSE).