SELECT
    S.month,
    Coalesce(S.sales, 0) AS sales,
    Coalesce(P.purchases, 0) AS purchases,
    Coalesce(S.tax1, 0) AS tax1,
    Coalesce(S.tax2, 0) AS tax2,
    Coalesce(P.ptax, 0) AS ptax
FROM ( SELECT Date_format(date, '%Y-%m') month, Sum(total) sales, Sum(product_tax) tax1, Sum(order_tax) tax2  FROM sma_sales  WHERE date >= Date_sub(Now(), INTERVAL 50 month)  GROUP BY Date_format(date, '%Y-%m') ) S
         LEFT JOIN ( SELECT Date_format(date, '%Y-%m') month, Sum(product_tax) ptax, Sum(order_tax) otax, Sum(total) purchases  FROM sma_purchases GROUP BY Date_format(date, '%Y-%m') ) P
                   ON S.month = P.month
GROUP BY
    S.month
ORDER BY
    S.month