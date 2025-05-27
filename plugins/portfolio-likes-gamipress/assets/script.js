/**
 * Portfolio Likes Plugin JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Handle like button click
        $(document).on('click', '.wbcom-portfolio-like-button', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var postId = $button.data('post-id');
            
            // Prevent double clicks
            if ($button.hasClass('loading')) {
                return;
            }
            
            // Add loading state
            $button.addClass('loading');
            var originalText = $button.find('.like-text').text();
            $button.find('.like-text').text(wbcom_portfolio_likes.loading_text);
            
            // Send AJAX request
            $.ajax({
                url: wbcom_portfolio_likes.ajax_url,
                type: 'POST',
                data: {
                    action: 'wbcom_portfolio_like',
                    post_id: postId,
                    nonce: wbcom_portfolio_likes.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Update button state
                        if (response.data.liked) {
                            $button.addClass('liked success');
                            $button.find('.like-text').text(wbcom_portfolio_likes.liked_text);
                        } else {
                            $button.removeClass('liked');
                            $button.find('.like-text').text(wbcom_portfolio_likes.like_text);
                        }
                        
                        // Update count with animation
                        var $count = $button.find('.like-count');
                        var currentCount = parseInt($count.text());
                        var newCount = response.data.likes_count;
                        
                        // Animate count change
                        if (currentCount !== newCount) {
                            $count.fadeOut(200, function() {
                                $(this).text(newCount).fadeIn(200);
                            });
                        }
                        
                        // Remove success class after animation
                        setTimeout(function() {
                            $button.removeClass('success');
                        }, 600);
                        
                        // Show notification (optional)
                        if (typeof wbcom_portfolio_likes.show_notifications !== 'undefined' && wbcom_portfolio_likes.show_notifications) {
                            showNotification(response.data.message, 'success');
                        }
                    } else {
                        // Handle error
                        $button.find('.like-text').text(originalText);
                        if (response.data && response.data.message) {
                            showNotification(response.data.message, 'error');
                        }
                    }
                },
                error: function() {
                    // Handle error
                    $button.find('.like-text').text(originalText);
                    showNotification('An error occurred. Please try again.', 'error');
                },
                complete: function() {
                    // Remove loading state
                    $button.removeClass('loading');
                }
            });
        });
        
        // Optional: Add visual feedback on hover
        $(document).on('mouseenter', '.wbcom-portfolio-like-button', function() {
            $(this).find('.like-icon').addClass('animate');
        }).on('mouseleave', '.wbcom-portfolio-like-button', function() {
            $(this).find('.like-icon').removeClass('animate');
        });
        
        // Notification function (optional)
        function showNotification(message, type) {
            // Skip if notifications are disabled
            if (typeof wbcom_portfolio_likes.show_notifications === 'undefined' || !wbcom_portfolio_likes.show_notifications) {
                return;
            }
            
            var $notification = $('<div class="wbcom-portfolio-like-notification ' + type + '">' + message + '</div>');
            
            $('body').append($notification);
            
            // Position notification
            $notification.css({
                position: 'fixed',
                top: '20px',
                right: '20px',
                background: type === 'success' ? '#4CAF50' : '#f44336',
                color: 'white',
                padding: '15px 25px',
                borderRadius: '4px',
                boxShadow: '0 2px 5px rgba(0,0,0,0.2)',
                zIndex: '9999',
                display: 'none'
            });
            
            // Show and hide notification
            $notification.fadeIn(300).delay(2000).fadeOut(300, function() {
                $(this).remove();
            });
        }
        
        // Optional: Keyboard support
        $(document).on('keypress', '.wbcom-portfolio-like-button', function(e) {
            if (e.which === 13 || e.which === 32) { // Enter or Space
                e.preventDefault();
                $(this).click();
            }
        });
    });

})(jQuery);