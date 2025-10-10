# 🚀 Start Here - Laravel Deployment Guide

Welcome! This guide will help you deploy your Laravel backend to your Ubuntu 20.04 VPS.

---

## 📋 What You Have

Your Laravel application with:
- **PHP Version**: 8.4.13 (local) → 8.3 (server)
- **Database**: PostgreSQL
- **Laravel Version**: 12.0
- **Composer**: 2.8.12

---

## 📦 Deployment Files Created

I've created several files to help you deploy:

1. **DEPLOYMENT.md** - Complete detailed deployment guide
2. **QUICK-DEPLOY.md** - Quick reference for common tasks
3. **DEPLOYMENT-CHECKLIST.md** - Step-by-step checklist
4. **deploy-server-setup.sh** - Automated server setup script
5. **deploy-app.sh** - Application deployment script
6. **deploy-from-local.sh** - Deploy from your Mac to VPS

---

## 🎯 Quick Start (3 Easy Steps)

### Step 1: Prepare Your Information

You'll need:
- ✅ VPS IP address
- ✅ SSH access (username/password or SSH key)
- ✅ Domain name (optional)
- ✅ Database password (create a strong one)

### Step 2: Run the Setup Script

From your Mac terminal:

```bash
cd /Users/sadeghmbp/Downloads/myDocuments/_develop/_fs_dev/hakimyarFusion/laraend

# Upload and run the server setup
./deploy-from-local.sh YOUR_VPS_IP setup
```

This will:
- Install PHP 8.3, PostgreSQL, Nginx, Composer
- Create database and user
- Configure the server
- Setup firewall

### Step 3: Deploy Your Application

```bash
# Deploy your Laravel application
./deploy-from-local.sh YOUR_VPS_IP deploy
```

This will:
- Upload your code to the VPS
- Install dependencies
- Run migrations
- Configure everything

---

## 🔧 Alternative Methods

### Method A: Automated (Recommended) ⭐
Use the scripts provided (as shown above)

### Method B: Using Git
1. Push your code to GitHub/GitLab
2. Run setup script on VPS
3. Clone your repository on VPS
4. Follow deployment steps

### Method C: Manual
Follow the detailed steps in `DEPLOYMENT.md`

---

## 📝 Important Notes

### Before Deployment

1. **Create a `.env` file** on your VPS with production settings:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=hakimyar_fusion
   DB_USERNAME=hakimyar_user
   DB_PASSWORD=your_secure_password
   ```

2. **Database Credentials**: The setup script will ask you to create:
   - Database name (e.g., `hakimyar_fusion`)
   - Database username (e.g., `hakimyar_user`)
   - Database password (create a strong one!)

3. **Domain Name** (optional but recommended):
   - Point your domain's A record to your VPS IP
   - Setup SSL certificate after deployment

---

## 🔐 Security Checklist

After deployment, ensure:
- [ ] `APP_DEBUG=false` in production `.env`
- [ ] Strong database password used
- [ ] Firewall enabled (UFW)
- [ ] SSL certificate installed (if using domain)
- [ ] File permissions set correctly
- [ ] Regular backups configured

---

## 📚 Documentation Files

| File | Purpose |
|------|---------|
| `START-HERE.md` | This file - quick overview |
| `DEPLOYMENT.md` | Complete deployment guide with all details |
| `QUICK-DEPLOY.md` | Quick reference and common commands |
| `DEPLOYMENT-CHECKLIST.md` | Step-by-step checklist |
| `deploy-server-setup.sh` | Automated server setup (run on VPS) |
| `deploy-app.sh` | Application deployment (run on VPS) |
| `deploy-from-local.sh` | Deploy from Mac to VPS |

---

## 🚦 Deployment Process Flow

```
┌─────────────────────┐
│  Your Mac (Local)   │
│  PHP 8.4.13         │
│  PostgreSQL         │
└──────────┬──────────┘
           │
           │ ./deploy-from-local.sh YOUR_VPS_IP setup
           ▼
┌─────────────────────┐
│  Ubuntu 20.04 VPS   │
│  Install PHP 8.3    │
│  Install PostgreSQL │
│  Install Nginx      │
│  Configure Server   │
└──────────┬──────────┘
           │
           │ ./deploy-from-local.sh YOUR_VPS_IP deploy
           ▼
┌─────────────────────┐
│  Deploy Laravel App │
│  Install deps       │
│  Run migrations     │
│  Configure          │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  ✅ App Running!    │
│  http://YOUR_VPS_IP │
└─────────────────────┘
```

---

## 🆘 Need Help?

### Common Issues

**"Cannot connect to VPS"**
- Check VPS IP address
- Ensure SSH is enabled
- Try: `ssh root@YOUR_VPS_IP`

**"Database connection failed"**
- Verify PostgreSQL is running
- Check credentials in `.env`
- Test: `sudo systemctl status postgresql`

**"500 Internal Server Error"**
- Check Laravel logs: `tail -f storage/logs/laravel.log`
- Verify file permissions
- Clear caches

### Where to Look

1. **Laravel Logs**: `/var/www/hakimyar-fusion/storage/logs/laravel.log`
2. **Nginx Logs**: `/var/log/nginx/error.log`
3. **PHP-FPM Logs**: `/var/log/php8.3-fpm.log`

---

## 📞 Quick Commands Reference

### Deploy Updates
```bash
./deploy-from-local.sh YOUR_VPS_IP update
```

### SSH into VPS
```bash
ssh root@YOUR_VPS_IP
```

### View Laravel Logs
```bash
ssh root@YOUR_VPS_IP
tail -f /var/www/hakimyar-fusion/storage/logs/laravel.log
```

### Restart Services
```bash
ssh root@YOUR_VPS_IP
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm
```

---

## 🎓 Learning Resources

- **Laravel Docs**: https://laravel.com/docs
- **PostgreSQL Docs**: https://www.postgresql.org/docs/
- **Nginx Docs**: https://nginx.org/en/docs/

---

## ✅ Next Steps

1. **Read** `DEPLOYMENT.md` for detailed understanding
2. **Prepare** your VPS credentials and database password
3. **Run** `./deploy-from-local.sh YOUR_VPS_IP setup`
4. **Deploy** `./deploy-from-local.sh YOUR_VPS_IP deploy`
5. **Test** your application
6. **Setup** SSL certificate (if using domain)
7. **Configure** backups

---

## 🎉 Ready to Deploy?

```bash
# Make sure you're in the project directory
cd /Users/sadeghmbp/Downloads/myDocuments/_develop/_fs_dev/hakimyarFusion/laraend

# Start deployment
./deploy-from-local.sh YOUR_VPS_IP setup
```

**Good luck! 🚀**

---

*Last Updated: October 5, 2025*


