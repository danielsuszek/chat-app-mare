SELECT c1.id AS conv_id, p1.user_id AS my_id, p2.user_id AS other_user_id, created_at, u1.username AS my_name, u2.username AS otherUser_name  
FROM conversation AS c1
JOIN participant AS p1 ON p1.conversation_id = c1.id AND p1.user_id = 1
JOIN participant AS p2 ON p2.conversation_id = c1.id AND p2.user_id <> 1
LEFT JOIN message AS m ON m.conversation_id = c1.last_message_id
JOIN user AS u1 ON u1.id = p1.user_id
JOIN user AS u2 ON u2.id = p2.user_id
ORDER BY created_at DESC



