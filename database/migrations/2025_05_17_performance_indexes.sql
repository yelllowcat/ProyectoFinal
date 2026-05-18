-- Performance Optimization Indexes
-- Created: 2025-05-17
-- Run this migration against your production database:
-- mysql -u your_user -p unired_DB < database/migrations/2025_05_17_performance_indexes.sql
--
-- NOTE: If you run this twice, you may get "Duplicate key name" errors for existing indexes.
--       This is safe to ignore, or you can drop the indexes first if needed.

-- =====================================================
-- COMPOSITE INDEXES (feed, profile, hashtag pages)
-- =====================================================

-- Feed ordering + active filtering
CREATE INDEX idx_posts_active_created ON posts(active, created_at DESC);

-- User profile posts
CREATE INDEX idx_posts_user_active_created ON posts(user_id, active, created_at DESC);

-- Comments per post (bulk loading first N comments)
CREATE INDEX idx_comments_post_active_created ON comments(post_id, active, created_at);

-- =====================================================
-- FRIEND REQUEST INDEXES (very hot path for search + friends page)
-- =====================================================

CREATE INDEX idx_friend_requests_receiver ON friend_requests(receiver_id, status);
CREATE INDEX idx_friend_requests_sender ON friend_requests(sender_id, status);

-- =====================================================
-- FULLTEXT INDEXES (replace LIKE '%term%' full table scans)
-- =====================================================

-- IMPORTANT: FULLTEXT indexes require MySQL 5.6+ with InnoDB
-- If you get "FULLTEXT index is not supported for your storage engine",
-- ensure your MySQL version supports InnoDB FULLTEXT.

CREATE FULLTEXT INDEX idx_posts_content ON posts(content);
CREATE FULLTEXT INDEX idx_users_search ON users(full_name, email);

-- =====================================================
-- NOTES
-- =====================================================
-- These indexes dramatically speed up:
-- 1. The main feed (ORDER BY created_at DESC WHERE active=1)
-- 2. Profile pages (user_id + active + created_at)
-- 3. Comment loading per post (post_id + active + created_at)
-- 4. Search queries (MATCH ... AGAINST instead of LIKE '%term%')
-- 5. Friendship status checks (receiver_id + status, sender_id + status)

-- To verify indexes were created:
-- SHOW INDEX FROM posts;
-- SHOW INDEX FROM users;
-- SHOW INDEX FROM comments;
-- SHOW INDEX FROM friend_requests;
