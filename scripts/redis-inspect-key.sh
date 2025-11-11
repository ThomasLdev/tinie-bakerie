#!/bin/bash
# Redis Key Inspector

if [ -z "$1" ]; then
    echo "Usage: ./redis-inspect-key.sh <key_pattern>"
    echo ""
    echo "Examples:"
    echo "  ./redis-inspect-key.sh '*post*'           # Find all keys with 'post'"
    echo "  ./redis-inspect-key.sh 'specific_key'     # Inspect specific key"
    echo ""
    echo "Available keys:"
    docker compose exec redis redis-cli KEYS '*' | head -10
    exit 1
fi

KEY_PATTERN="$1"

echo "=== Redis Key Inspector ==="
echo ""

# Find matching keys
echo "🔍 Searching for keys matching: $KEY_PATTERN"
KEYS=$(docker compose exec redis redis-cli KEYS "$KEY_PATTERN")

if [ -z "$KEYS" ]; then
    echo "❌ No keys found matching pattern: $KEY_PATTERN"
    exit 1
fi

echo "Found keys:"
echo "$KEYS"
echo ""

# Inspect each key
for KEY in $KEYS; do
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "📋 Key: $KEY"
    echo ""
    
    # Get key type
    TYPE=$(docker compose exec redis redis-cli TYPE "$KEY" | tr -d '\r')
    echo "Type: $TYPE"
    
    # Get TTL
    TTL=$(docker compose exec redis redis-cli TTL "$KEY" | tr -d '\r')
    if [ "$TTL" = "-1" ]; then
        echo "TTL: No expiration"
    elif [ "$TTL" = "-2" ]; then
        echo "TTL: Key doesn't exist"
    else
        echo "TTL: $TTL seconds"
    fi
    
    # Get value based on type
    echo ""
    echo "Content:"
    case $TYPE in
        string)
            docker compose exec redis redis-cli GET "$KEY"
            ;;
        list)
            docker compose exec redis redis-cli LRANGE "$KEY" 0 -1
            ;;
        set)
            docker compose exec redis redis-cli SMEMBERS "$KEY"
            ;;
        zset)
            docker compose exec redis redis-cli ZRANGE "$KEY" 0 -1 WITHSCORES
            ;;
        hash)
            docker compose exec redis redis-cli HGETALL "$KEY"
            ;;
        *)
            echo "Unknown type: $TYPE"
            ;;
    esac
    echo ""
done
