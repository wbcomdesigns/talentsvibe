# Portfolio Likes Plugin - Setup & Usage Guide

## Plugin Features

This plugin adds a complete like system for portfolio items with GamiPress integration:

- **Like Button**: Automatically added to all portfolio items
- **Guest Support**: Non-logged-in users can like using IP tracking
- **User Support**: Logged-in users' likes are tracked by user ID
- **Milestone Badges**: Automatic badge triggers at 50 and 500 likes
- **Admin Column**: See likes count in portfolio admin
- **No Duplicate Likes**: Each user/IP can only like once per portfolio

## Installation

### Method 1: Upload as Plugin

1. Create folder structure:
```
/wp-content/plugins/portfolio-likes-gamipress/
├── portfolio-likes-gamipress.php (main plugin file)
├── assets/
│   ├── style.css
│   └── script.js
└── languages/
```

2. Upload files to the folder
3. Activate plugin in WordPress admin

### Method 2: Single File (Quick Test)

1. Copy just the main PHP code to a file named `portfolio-likes-gamipress.php`
2. Create the assets folder and files manually
3. Upload and activate

## File Structure

```
portfolio-likes-gamipress/
├── portfolio-likes-gamipress.php    # Main plugin file
├── assets/
│   ├── style.css                    # Button styles
│   └── script.js                    # AJAX functionality
├── languages/                       # Translation files (optional)
└── readme.txt                       # Plugin information (optional)
```

## Setting Up GamiPress Achievements

### 1. Create "Engagement Guru" Badge (50+ Likes)

1. Go to **GamiPress → Achievements**
2. Click **"Add New"**
3. Configure:
   - **Title**: Engagement Guru
   - **Description**: Received 50+ total likes on portfolio items
   - **Points Award**: 100 (optional bonus points)
   
4. In **Required Steps**, add:
   - **Trigger**: "Reach 50 total likes on portfolios (Engagement Guru)"
   - **Times**: 1

5. Upload badge image (optional)
6. **Publish**

### 2. Create "Top Creator" Badge (500+ Likes)

1. Go to **GamiPress → Achievements**
2. Click **"Add New"**
3. Configure:
   - **Title**: Top Creator
   - **Description**: Received 500+ total likes on portfolio items
   - **Points Award**: 500 (optional bonus points)
   
4. In **Required Steps**, add:
   - **Trigger**: "Reach 500 total likes on portfolios (Top Creator)"
   - **Times**: 1

5. Upload badge image (optional)
6. **Publish**

## Available GamiPress Triggers

The plugin adds these triggers to GamiPress:

1. **Reach 50 total likes on portfolios (Engagement Guru)** - Fires once when author reaches 50 total likes
2. **Reach 500 total likes on portfolios (Top Creator)** - Fires once when author reaches 500 total likes
3. **Get a like on a portfolio item** - Fires every time a portfolio gets a like
4. **Get a specific number of likes on portfolios** - For custom achievements

## How the Like System Works

### For Logged-In Users:
- Likes are tracked by user ID
- Users can like/unlike portfolios
- One like per portfolio per user

### For Guests:
- Likes are tracked by IP address
- Guests can like/unlike portfolios
- One like per portfolio per IP

### Database Storage:
- Creates table `wp_portfolio_likes`
- Stores: post_id, user_id (or IP), timestamp
- Prevents duplicate likes

## Template Functions

### Display Like Button Manually:
```php
// In your portfolio template
<?php portfolio_likes_button(); ?>

// Or for specific post
<?php portfolio_likes_button( $post_id ); ?>
```

### Get Likes Count:
```php
// Get likes for specific portfolio
$likes = get_portfolio_likes_count( $post_id );
echo 'This portfolio has ' . $likes . ' likes';

// Get total likes for an author
$total_likes = get_author_portfolio_likes( $user_id );
echo 'Author has ' . $total_likes . ' total likes';
```

### Check if Current User Liked:
```php
$liked = Portfolio_Likes_GamiPress::get_instance()->has_user_liked( $post_id );
if ( $liked ) {
    echo 'You liked this!';
}
```

## Customization

### Change Button Position:

Remove automatic placement:
```php
remove_filter( 'the_content', array( Portfolio_Likes_GamiPress::get_instance(), 'add_like_button' ), 20 );
```

Then add manually in your template.

### Custom Milestones:

Add more milestones:
```php
add_action( 'init', function() {
    // Check for 100 likes milestone
    add_action( 'portfolio_likes_milestone_reached', function( $author_id, $threshold ) {
        if ( $threshold === 100 && function_exists( 'gamipress_award_achievement' ) ) {
            // Award custom achievement
            gamipress_award_achievement( 123, $author_id ); // 123 = achievement ID
        }
    }, 10, 2 );
});
```

### Style Customization:

Override styles in your theme:
```css
/* Custom like button */
.portfolio-like-button {
    background: #your-color;
    /* your styles */
}

/* Liked state */
.portfolio-like-button.liked {
    background: #your-liked-color;
}
```

### Add Notifications:

Enable notifications:
```javascript
// In your theme's JS
portfolio_likes.show_notifications = true;
```

## Shortcodes

### Display Total Likes:
```
[portfolio_author_likes user_id="123"]
```

### Display Like Button:
```
[portfolio_like_button post_id="456"]
```

## Admin Features

### Portfolio Admin Column:
- Shows likes count for each portfolio
- Sortable column
- Quick view of popular items

### Database Cleanup:
The plugin includes cleanup on uninstall. To manually clean:
```sql
DROP TABLE IF EXISTS wp_portfolio_likes;
DELETE FROM wp_usermeta WHERE meta_key LIKE 'portfolio_likes_milestone_%';
```

## Troubleshooting

### Likes Not Recording?

1. Check JavaScript console for errors
2. Verify AJAX URL is correct
3. Check if table `wp_portfolio_likes` exists
4. Test with logged-in and guest users

### Badges Not Awarding?

1. Verify GamiPress is active
2. Check achievement triggers are set correctly
3. Look in GamiPress → Logs
4. Ensure achievements are published

### Button Not Showing?

1. Check if post type is 'portfolio'
2. Verify `the_content` filter is running
3. Try manual placement with `portfolio_likes_button()`

## Performance Considerations

- Likes are cached per page load
- Database indexes on post_id and user_id
- Minimal queries per page
- AJAX requests are throttled client-side

## Security Features

- Nonce verification on all AJAX requests
- Data sanitization and validation
- SQL injection prevention
- XSS protection

## Future Enhancements Ideas

1. **Like Statistics Dashboard**
2. **Email Notifications at Milestones**
3. **Weekly/Monthly Like Reports**
4. **Social Sharing Integration**
5. **Like Animation Options**
6. **Bulk Like Management**
7. **Export Likes Data**

## Support

For issues or customization needs:
1. Check browser console for JavaScript errors
2. Enable WP_DEBUG for PHP errors
3. Check GamiPress logs for achievement issues
4. Verify database table creation