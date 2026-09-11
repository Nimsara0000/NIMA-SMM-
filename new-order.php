<?php
require_once 'includes/auth.php';
require_once 'includes/api.php';
requireLogin();
$user = currentUser();
$api = new ProviderApi();

$services = $pdo->query("SELECT * FROM services WHERE status='active' ORDER BY category, name")->fetchAll();
$selectedId = (int)($_GET['service'] ?? 0);

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceId = (int)($_POST['service'] ?? 0);
    $link = trim($_POST['link'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 0);
    $extra = [];
    foreach (['runs','interval','keywords','comments','usernames','hashtags','hashtag','username','media','answer_number','groups','min','max','posts','old_posts','delay','expiry'] as $k) {
        if (!empty($_POST[$k])) $extra[$k] = $_POST[$k];
    }

    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND status='active'");
    $stmt->execute([$serviceId]);
    $service = $stmt->fetch();

    if (!$service) $message = '<div class="toast error" style="position:static">Invalid service selected.</div>';
    elseif (!$link) $message = '<div class="toast error" style="position:static">Link is required.</div>';
    elseif ($quantity < $service['min'] || $quantity > $service['max']) $message = '<div class="toast error" style="position:static">Quantity must be between ' . $service['min'] . ' and ' . $service['max'] . '.</div>';
    else {
        $charge = ($quantity / 1000) * $service['rate'];
        if ($charge > $user['balance']) $message = '<div class="toast error" style="position:static">Insufficient balance. You need $' . number_format($charge, 4) . '</div>';
        else {
            $orderData = array_merge(['service' => $service['provider_service_id'], 'link' => $link, 'quantity' => $quantity], $extra);
            $result = $api->order($orderData);
            if (isset($result->order)) {
                $pdo->beginTransaction();
                $newBalance = $user['balance'] - $charge;
                $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?")->execute([$newBalance, $user['id']]);
                $pdo->prepare("INSERT INTO orders (user_id,order_id,service_id,link,quantity,charge,status) VALUES (?,?,?,?,?,?,'Pending')")
                    ->execute([$user['id'], $result->order, $serviceId, $link, $quantity, $charge]);
                $pdo->prepare("INSERT INTO transactions (user_id,type,amount,balance_after,description) VALUES (?,'order',?,?,?)")
                    ->execute([$user['id'], -$charge, $newBalance, 'Order #' . $result->order . ' — ' . $service['name']]);
                $pdo->commit();
                $message = '<div class="toast success" style="position:static">✅ Order placed! ID: ' . htmlspecialchars($result->order) . '</div>';
                $user = currentUser();
            } else {
                $message = '<div class="toast error" style="position:static">❌ ' . htmlspecialchars($result->error ?? 'Order failed') . '</div>';
            }
        }
    }
}
$pageTitle = 'New Order — ' . SITE_NAME;
include 'includes/header.php';
?>
<div class="card">
    <h2>Place New Order</h2>
    <p class="sub">Fill in the details below and submit your order</p>
    <?= $message ?>
    <form method="POST" id="orderForm">
        <div class="field">
            <label>Service</label>
            <select name="service" id="serviceSelect" required>
                <option value="">Select a service</option>
                <?php foreach ($services as $s): ?>
                    <option value="<?= $s['id'] ?>" data-rate="<?= $s['rate'] ?>" data-type="<?= $s['type'] ?>" <?= $selectedId==$s['id']?'selected':'' ?>>
                        #<?= $s['provider_service_id'] ?> — <?= htmlspecialchars($s['name']) ?> ($<?= $s['rate'] ?>/1k)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field"><label>Link</label><input type="url" name="link" placeholder="https://instagram.com/p/..." required></div>
        <div class="row">
            <div class="field"><label>Quantity</label><input type="number" name="quantity" id="qtyInput" placeholder="100" min="1" required></div>
            <div class="field"><label>Estimated Charge</label><input type="text" id="chargePreview" readonly value="$0.00"></div>
        </div>
        <div id="extraFields"></div>
        <button class="btn-primary">Place Order</button>
    </form>
</div>

<script>
const FIELD_DEFS = {
  keywords:{label:'Keywords (comma separated)',type:'text',ph:'test, testing'},
  usernames:{label:'Usernames (one per line)',type:'textarea',ph:'test\ntesting'},
  hashtags:{label:'Hashtags',type:'text',ph:'#goodphoto'},
  hashtag:{label:'Hashtag',type:'text',ph:'test'},
  username:{label:'Username',type:'text',ph:'username'},
  media:{label:'Media URL',type:'text',ph:'https://instagram.com/p/...'},
  comments:{label:'Comments (one per line)',type:'textarea',ph:'good pic\ngreat photo'},
  answer_number:{label:'Answer Number',type:'number',ph:'7'},
  groups:{label:'Groups (one per line)',type:'textarea',ph:'group1\ngroup2'},
  min:{label:'Min',type:'number',ph:'100'}, max:{label:'Max',type:'number',ph:'110'},
  posts:{label:'Posts (0 = all)',type:'number',ph:'0'}, old_posts:{label:'Old Posts',type:'number',ph:'5'},
  delay:{label:'Delay (minutes)',type:'number',ph:'30'}, expiry:{label:'Expiry (MM/DD/YYYY)',type:'text',ph:'11/11/2026'},
  runs:{label:'Runs',type:'number',ph:'2'}, interval:{label:'Interval (minutes)',type:'number',ph:'5'}
};
const TYPE_FIELDS = {
  'Default':[], 'Package':[], 'SEO':['keywords'],
  'Mentions':['usernames','hashtags'], 'Mentions Custom List':['usernames'],
  'Mentions Hashtag':['hashtag'], 'Mentions User Followers':['username'],
  'Mentions Media Likers':['media'], 'Custom Comments':['comments'],
  'Comment Likes':['username'], 'Poll':['answer_number'], 'Invites from Groups':['groups'],
  'Subscriptions':['username','min','max','posts','old_posts','delay','expiry'], 'Drip-feed':['runs','interval']
};
const NO_QTY = ['Package','Custom Comments','Mentions Custom List','Subscriptions'];
let RATE = 0;

function onServiceChange() {
    const sel = document.getElementById('serviceSelect');
    const opt = sel.options[sel.selectedIndex];
    const type = opt.dataset.type || 'Default';
    RATE = parseFloat(opt.dataset.rate || 0);
    const fields = TYPE_FIELDS[type] || [];
    const showQty = !NO_QTY.includes(type);
    document.querySelector('.row').style.display = showQty ? '' : 'none';
    document.getElementById('qtyInput').required = showQty;
    document.getElementById('extraFields').innerHTML = fields.map(k => {
        const f = FIELD_DEFS[k]; if (!f) return '';
        const inp = f.type==='textarea' ? `<textarea name="${k}" placeholder="${f.ph}"></textarea>` : `<input type="${f.type}" name="${k}" placeholder="${f.ph}">`;
        return `<div class="field"><label>${f.label}</label>${inp}</div>`;
    }).join('');
    updateCharge();
}
function updateCharge() {
    const q = parseFloat(document.getElementById('qtyInput').value || 0);
    const c = (q/1000)*RATE;
    document.getElementById('chargePreview').value = '$' + (isFinite(c)&&c>0 ? c.toFixed(4) : '0.00');
}
document.getElementById('serviceSelect').addEventListener('change', onServiceChange);
document.getElementById('qtyInput').addEventListener('input', updateCharge);
onServiceChange();
</script>
<?php include 'includes/footer.php'; ?>
