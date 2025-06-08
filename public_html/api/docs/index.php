<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

$apiDocs = [
    'items' => [
        'GET /items/api/get.php' => [
            'description' => 'Get item details',
            'parameters' => ['id' => 'Item ID (optional)'],
            'response' => ['success' => 'boolean', 'data' => 'object|array']
        ],
        'POST /items/api/create.php' => [
            'description' => 'Create new item',
            'parameters' => [
                'title' => 'string',
                'description' => 'string',
                'category' => 'string',
                'location' => 'string'
            ]
        ]
    ],
    'claims' => [
        'POST /claims/api/create.php' => [
            'description' => 'Submit claim for item',
            'parameters' => [
                'item_id' => 'integer',
                'description' => 'string'
            ]
        ]
    ]
];

$pageTitle = 'API Documentation';
include '../../includes/header.php';
?>

<div class="container api-docs">
    <h1>API Documentation</h1>
    
    <?php foreach ($apiDocs as $section => $endpoints): ?>
        <section class="api-section">
            <h2><?= ucfirst($section) ?></h2>
            <?php foreach ($endpoints as $endpoint => $details): ?>
                <div class="endpoint">
                    <h3><?= $endpoint ?></h3>
                    <p><?= $details['description'] ?></p>
                    
                    <?php if (!empty($details['parameters'])): ?>
                        <h4>Parameters:</h4>
                        <ul>
                            <?php foreach ($details['parameters'] as $param => $type): ?>
                                <li><code><?= $param ?></code>: <?= $type ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endforeach; ?>
</div>

<?php include '../../includes/footer.php'; ?>
