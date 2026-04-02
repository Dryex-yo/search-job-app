<?php

require 'vendor/autoload.php';

// Load .env file
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== OpenAI API Test ===\n\n";

$apiKey = $_ENV['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY') ?? null;

if (!$apiKey) {
    echo "❌ ERROR: OPENAI_API_KEY tidak ditemukan di .env\n";
    echo "Debug: Checking .env file...\n";
    $envContent = file_get_contents('.env');
    if (strpos($envContent, 'OPENAI_API_KEY') !== false) {
        echo "✅ OPENAI_API_KEY ada di .env tapi tidak ter-load\n";
    }
    exit(1);
}

echo "✅ API Key ditemukan\n";
echo "Key preview: " . substr($apiKey, 0, 20) . "...\n\n";

try {
    $client = \OpenAI::client($apiKey);
    
    echo "🔄 Mengirim request ke OpenAI...\n";
    
    $response = $client->chat()->create([
        'model' => 'gpt-4o-mini',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are a helpful assistant. Reply with just a haiku.'
            ],
            [
                'role' => 'user',
                'content' => 'Write a haiku about software development'
            ]
        ],
        'max_tokens' => 100,
        'temperature' => 0.7,
    ]);

    echo "\n✅ Response received!\n";
    echo "=================================\n";
    echo "Model: " . $response->model . "\n";
    echo "Usage:\n";
    echo "  - Prompt tokens: " . $response->usage->promptTokens . "\n";
    echo "  - Completion tokens: " . $response->usage->completionTokens . "\n";
    echo "  - Total tokens: " . $response->usage->totalTokens . "\n";
    echo "\nResponse:\n";
    echo $response->choices[0]->message->content . "\n";
    echo "=================================\n\n";
    echo "✅ OpenAI API is working correctly!\n";

} catch (\OpenAI\Exceptions\ErrorException $e) {
    echo "❌ OpenAI API Error:\n";
    echo "Status: " . $e->getCode() . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    
    if (strpos($e->getMessage(), '401') !== false || strpos($e->getMessage(), 'Unauthorized') !== false) {
        echo "\n⚠️ API Key invalid atau expired. Regenerate key baru di https://platform.openai.com/account/api-keys\n";
    } elseif (strpos($e->getMessage(), '429') !== false || strpos($e->getMessage(), 'rate limit') !== false) {
        echo "\n⚠️ Rate limit exceeded. Wait a moment then retry.\n";
    } elseif (strpos($e->getMessage(), 'quota') !== false) {
        echo "\n⚠️ Quota exceeded. Upgrade API plan di https://platform.openai.com/account/billing/overview\n";
    }
    exit(1);

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
