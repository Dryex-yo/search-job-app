#!/bin/bash

# Laravel Reverb WebSocket Server Starter
# Usage: ./start-reverb.sh (or php artisan reverb:start on Windows)

# Colors for terminal output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║${NC}      🚀 Laravel Reverb - Real-time WebSocket Server     ${BLUE}║${NC}"
echo -e "${BLUE}╠════════════════════════════════════════════════════════════╣${NC}"
echo -e "${BLUE}║${NC}  Starting WebSocket server for real-time notifications ${BLUE}║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"

echo ""
echo -e "${YELLOW}⚙️  Configuration:${NC}"
echo -e "   Host: ${GREEN}localhost${NC}"
echo -e "   Port: ${GREEN}8080${NC}"
echo -e "   Protocol: ${GREEN}ws://localhost:8080${NC}"
echo ""
echo -e "${YELLOW}📊 Features enabled:${NC}"
echo -e "   ✓ Real-time application notifications"
echo -e "   ✓ Live dashboard counter updates"
echo -e "   ✓ Sound notifications ('ting' effect)"
echo -e "   ✓ Auto-reconnection on disconnect"
echo ""

# Start the Reverb server
echo -e "${YELLOW}🔌 Starting Reverb server...${NC}"
echo -e "   ${GREEN}php artisan reverb:start${NC}"
echo ""
echo -e "${GREEN}✅ Server is running!${NC}"
echo ""
echo -e "${YELLOW}📝 Next steps:${NC}"
echo -e "   1. Open Admin Dashboard in browser"
echo -e "   2. Submit a new application"
echo -e "   3. Watch dashboard update in real-time! 🎉"
echo ""
echo -e "${YELLOW}🔍 Debug mode:${NC}"
echo -e "   Use: ${GREEN}php artisan reverb:start --debug${NC}"
echo -e "   To see all WebSocket events and connections"
echo ""
echo -e "${YELLOW}💡 Tips:${NC}"
echo -e "   • Keep this server running while developing"
echo -e "   • Use separate terminal windows for:"
echo -e "     - npm run dev (frontend Vite server)"
echo -e "     - php artisan reverb:start (WebSocket server)"
echo -e "     - php artisan serve (Laravel app server)"
echo ""
echo -e "${YELLOW}⚠️  Important:${NC}"
echo -e "   • Don't stop this process (press Ctrl+C to stop)"
echo -e "   • In production, use Supervisor to auto-restart"
echo ""

php artisan reverb:start
