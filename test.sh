#!/bin/bash

#############################################
# Cipi Testing Script
# Tests bash syntax and basic functionality
#############################################

set -e

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Cipi Testing Suite"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Test counter
TESTS_PASSED=0
TESTS_FAILED=0

# Test bash syntax
test_syntax() {
    local file=$1
    echo -n "Testing syntax: $file ... "
    
    if bash -n "$file" 2>/dev/null; then
        echo -e "${GREEN}✓ PASS${NC}"
        ((TESTS_PASSED++))
        return 0
    else
        echo -e "${RED}✗ FAIL${NC}"
        bash -n "$file"
        ((TESTS_FAILED++))
        return 1
    fi
}

# Test if file is executable
test_executable() {
    local file=$1
    echo -n "Testing executable: $file ... "
    
    if [ -x "$file" ]; then
        echo -e "${GREEN}✓ PASS${NC}"
        ((TESTS_PASSED++))
        return 0
    else
        echo -e "${RED}✗ FAIL${NC}"
        ((TESTS_FAILED++))
        return 1
    fi
}

# Test if file exists
test_exists() {
    local file=$1
    echo -n "Testing exists: $file ... "
    
    if [ -f "$file" ]; then
        echo -e "${GREEN}✓ PASS${NC}"
        ((TESTS_PASSED++))
        return 0
    else
        echo -e "${RED}✗ FAIL${NC}"
        ((TESTS_FAILED++))
        return 1
    fi
}

echo "1. File Existence Tests"
echo "─────────────────────────────────────"
test_exists "cipi"
test_exists "install.sh"
test_exists "README.md"
test_exists "LICENSE"
test_exists "lib/storage.sh"
test_exists "lib/system.sh"
test_exists "lib/commands.sh"
test_exists "lib/app.sh"
test_exists "lib/domain.sh"
test_exists "lib/database.sh"
test_exists "lib/php.sh"
test_exists "lib/nginx.sh"
test_exists "lib/service.sh"
test_exists "lib/update.sh"
test_exists "lib/templates.sh"
echo ""

echo "2. Executable Tests"
echo "─────────────────────────────────────"
test_executable "cipi"
test_executable "install.sh"
echo ""

echo "3. Bash Syntax Tests"
echo "─────────────────────────────────────"
test_syntax "cipi"
test_syntax "install.sh"

for file in lib/*.sh; do
    test_syntax "$file"
done
echo ""

echo "4. Content Tests"
echo "─────────────────────────────────────"

# Test if main script has proper shebang
echo -n "Testing shebang in cipi ... "
if head -n 1 cipi | grep -q "#!/bin/bash"; then
    echo -e "${GREEN}✓ PASS${NC}"
    ((TESTS_PASSED++))
else
    echo -e "${RED}✗ FAIL${NC}"
    ((TESTS_FAILED++))
fi

# Test if install.sh has proper shebang
echo -n "Testing shebang in install.sh ... "
if head -n 1 install.sh | grep -q "#!/bin/bash"; then
    echo -e "${GREEN}✓ PASS${NC}"
    ((TESTS_PASSED++))
else
    echo -e "${RED}✗ FAIL${NC}"
    ((TESTS_FAILED++))
fi

# Test if README has installation instructions
echo -n "Testing README has installation ... "
if grep -q "wget -O - https://raw.githubusercontent.com" README.md; then
    echo -e "${GREEN}✓ PASS${NC}"
    ((TESTS_PASSED++))
else
    echo -e "${RED}✗ FAIL${NC}"
    ((TESTS_FAILED++))
fi

echo ""

# Summary
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Test Summary"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "Tests Passed: ${GREEN}$TESTS_PASSED${NC}"
echo -e "Tests Failed: ${RED}$TESTS_FAILED${NC}"
echo ""

if [ $TESTS_FAILED -eq 0 ]; then
    echo -e "${GREEN}✓ All tests passed!${NC}"
    exit 0
else
    echo -e "${RED}✗ Some tests failed!${NC}"
    exit 1
fi

