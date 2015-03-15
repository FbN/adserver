SELECT DISTINCT   b.*
FROM      `ad_campaign_runtime` rt
LEFT JOIN ad_campaign c
ON        (
                    c.id = rt.campaign_id )
LEFT JOIN ad_campaign_referer_filter rf
ON        (
                    c.id = rf.campaign_id )
JOIN ad_banner b
WHERE     c.active = true
AND		  c.delivered<c.goal
AND       rt.start <= '2014-03-15 12:50:23'
AND       rt.end >= '2014-03-15 12:50:23'
AND       (
                    c.time_filter_active = false
          OR        (
                              d_sunday = true
                    AND       h16 = true ) )
AND       (
                    c.cookie=NULL
                              || c.cookie = 'cookieval3')
AND       ((
                              rf.campaign_id IS NULL)
          OR        ( (
                                        rf.hostname_only=true
                              AND       rf.referer = 'onlyhost')
                    OR        (
                                        rf.hostname_only=false
                              AND       rf.referer = 'fullreferer') )) 
AND (c.id=b.campaign_id)