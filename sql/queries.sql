mysql> select * from aaa_tag_pages where page_url = 'go.travelpn.com/checkout/CheckoutReview.do';
mysql> select * from aaa_tag_pages where page_url like 'go.travelpn.com/checkout/CheckoutReview.do';
mysql> select page_url, count(*) from aaa_tag_pages group by page_url having count(*) > 1;
mysql> select * from aaa_tag_pages where page_url = 'go.travelpn.com/checkout/PostDisplayContacts.do';
mysql> quit
