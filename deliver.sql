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


SELECT b.*                      FROM      `ad_campaign_runtime` rt
                                LEFT JOIN `ad_campaign` c
                                ON        ( c.id = rt.campaign_id )
                                LEFT JOIN `ad_campaign_referer_filter` rf
                                ON        ( c.id = rf.campaign_id )
                                JOIN `ad_banner` b
                                WHERE     
                                		(c.id=b.campaign_id)
                                AND
                                		(		(c.active = true AND c.time_filter_active = true AND c.d_sunday = true AND c.h16 = true AND ( b.width='100' AND b.height='200' )) 
                                		OR 		(c.active = true AND c.time_filter_active = false AND ( b.width='100' AND b.height='200' ))
                                		)
                                AND       c.delivered<c.goal
                                AND       rt.start <= '2015-03-22 16:18:31'
                                AND       rt.end >= '2015-03-22 16:18:31'                                
                                AND       (c.cookie IS NULL || c.cookie = NULL)
                                AND       ((
                                                              rf.campaign_id IS NULL)
                                          OR        ( (
                                                                        rf.hostname_only=true
                                                              AND       rf.referer = NULL)
                                                    OR        (
                                                                        rf.hostname_only=false
                                                              AND       rf.referer = NULL) ))
                        		LIMIT 3299,1
                        		
#curl -w '\nLookup time:\t%{time_namelookup}\nConnect time:\t%{time_connect}\nPreXfer time:\t%{time_pretransfer}\nStartXfer time:\t%{time_starttransfer}\n\nTotal time:\t%{time_total}\n' -o /dev/null -s http://adserver.loc/deliver?w=100&h=200&jsonp=call
























SELECT   b.id
FROM    (
        SELECT  @cnt := COUNT(*) + 1,
                @lim := 1
        FROM      `ad_campaign_runtime` rt
                                LEFT JOIN `ad_campaign` c
                                ON        ( c.id = rt.campaign_id )
                                LEFT JOIN `ad_campaign_referer_filter` rf
                                ON        ( c.id = rf.campaign_id )
                                JOIN `ad_banner` b
                                WHERE     
                                                  (c.id=b.campaign_id)
             AND       ( b.width='100' AND b.height='200' )
                                AND               (   
                                                        (c.active = true AND c.time_filter_active = true  AND c.d_sunday = true AND c.h16 = true) 
                         OR (c.active = true AND c.time_filter_active = false)
                        )
                                AND               c.delivered<c.goal
                                AND       rt.start <= '2015-03-22 18:07:15'
                                AND       rt.end >= '2015-03-22 18:07:15'
                                AND       (                     c.cookie IS NULL || c.cookie = NULL)
                                AND       ((
                                                              rf.campaign_id IS NULL)
                                          OR        ( (
                                                                        rf.hostname_only=true
                                                              AND       rf.referer = NULL)
                                                    OR        (
                                                                        rf.hostname_only=false
                                                              AND       rf.referer = NULL) ))
        
        ) vars
STRAIGHT_JOIN
        (
        SELECT  b2.id,
                @lim := @lim - 1
        FROM      `ad_campaign_runtime` rt2
                                LEFT JOIN `ad_campaign` c2
                                ON        ( c2.id = rt2.campaign_id )
                                LEFT JOIN `ad_campaign_referer_filter` rf2
                                ON        ( c2.id = rf2.campaign_id )
                                JOIN `ad_banner` b2
                                WHERE     
                                                  (c2.id=b2.campaign_id)
             AND       ( b2.width='100' AND b2.height='200' )
                                AND               (   
                                                        (c2.active = true AND c2.time_filter_active = true  AND c2.d_sunday = true AND c2.h16 = true) 
                         OR (c2.active = true AND c2.time_filter_active = false)
                        )
                                AND               c2.delivered<c2.goal
                                AND       rt2.start <= '2015-03-22 18:07:15'
                                AND       rt2.end >= '2015-03-22 18:07:15'
                                AND       (                     c2.cookie IS NULL || c2.cookie = NULL)
                                AND       ((
                                                              rf2.campaign_id IS NULL)
                                          OR        ( (
                                                                        rf2.hostname_only=true
                                                              AND       rf2.referer = NULL)
                                                    OR        (
                                                                        rf2.hostname_only=false
                                                              AND       rf2.referer = NULL) ))
        WHERE   (@cnt := @cnt - 1)
                AND RAND(20090301) < @lim / @cnt
        ) i

        
        
        
SELECT DISTINCT b.id
FROM      `ad_campaign_runtime` rt
JOIN       ad_campaign c
JOIN       ad_banner b
WHERE           
	(rt.start <= '2014-03-15 12:50:23' AND rt.end >= '2014-03-15 12:50:23')
AND ((c.active = true and c.time_filter_active = true AND d_sunday = true AND h16 = true) OR (c.active = true and c.time_filter_active = false))      
AND (c.delivered<c.goal)
AND c.id = b.campaign_id
AND c.id = rt.campaign_id
