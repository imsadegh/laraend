#!/bin/bash

# SSL Certificate Setup Script
# Run this script after your domain is pointing to your VPS

echo "🔒 Setting up SSL Certificate with Let's Encrypt..."

# Install Certbot
echo "📦 Installing Certbot..."
apt update
apt install -y certbot python3-certbot-nginx

# Get domain from user
read -p "Enter your domain name (e.g., example.com): " DOMAIN

# Obtain SSL certificate
echo "🔐 Obtaining SSL certificate for $DOMAIN..."
certbot --nginx -d $DOMAIN -d www.$DOMAIN --non-interactive --agree-tos --email admin@$DOMAIN

# Test certificate renewal
echo "🧪 Testing certificate renewal..."
certbot renew --dry-run

# Set up automatic renewal
echo "⏰ Setting up automatic renewal..."
(crontab -l 2>/dev/null; echo "0 12 * * * /usr/bin/certbot renew --quiet") | crontab -

echo "✅ SSL certificate setup completed!"
echo "🌐 Your site is now accessible at: https://$DOMAIN"
echo "🔄 Certificate will auto-renew every 90 days"

