PID_FILE=$0.pid
[ -f $PID_FILE ] && {
   pid=`cat $PID_FILE`
   ps -p $pid && {
    echo -e "MobifoneCrontab_renew is running ..." >> /video/www/halovietnam/wap/app/tmp/logs/MobifoneCrontab_renew.out.log
    exit
   }
   rm -f $PID_FILE
}
echo $$ > $PID_FILE
php /video/www/halovietnam/wap/app/cron_dispatcher.php /MobifoneCrontab/renew >> /video/www/halovietnam/wap/app/tmp/logs/MobifoneCrontab_renew.out.log 2>&1