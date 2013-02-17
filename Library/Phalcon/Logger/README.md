

```sql
CREATE TABLE `logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT NULL,
  `type` int(3) NOT NULL,
  `content` text,
  `created_at` int(18) unsigned NOT NULL,
  PRIMARY KEY (`id`)
)
```