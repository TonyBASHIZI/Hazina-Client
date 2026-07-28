<?php
require_once __DIR__ . '/includes/functions.php';

$pdo = getPDO();
$client = currentClientUser();

$errors = [];
$success = false;

$data = [
    'full_name' => $client['full_name'] ?? ($client['username'] ?? ''),
    'email' => $client['mail'] ?? '',
    'telephone' => $client['telephone'] ?? '',
    'ville' => '', 'pays' => '', 'domaine' => '', 'disponibilites' => '',
    'competences' => '', 'experience' => '', 'motivation' => '', 'langues' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();

    foreach (['full_name','email','telephone','ville','pays','domaine','disponibilites','competences','experience','motivation','langues'] as $field) {
        $data[$field] = trim($_POST[$field] ?? '');
    }

    if ($data['full_name'] === '') $errors[] = 'Le nom complet est obligatoire.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';
    if ($data['telephone'] === '') $errors[] = 'Le téléphone est obligatoire.';
    if ($data['motivation'] === '') $errors[] = 'Merci d\'expliquer ta motivation.';

    if (!$errors) {
        $dupCheck = $pdo->prepare(
            "SELECT COUNT(*) FROM volunteers WHERE email = :email AND status NOT IN ('refuse', 'inactif')"
        );
        $dupCheck->execute(['email' => $data['email']]);
        if ($dupCheck->fetchColumn() > 0) {
            $errors[] = 'Une candidature est déjà en cours avec cet email. Merci de patienter pendant son étude, ou contacte-nous directement si besoin.';
        }
    }

    $uploadedDocs = []; // [['type' => ..., 'path' => ..., 'original_name' => ...], ...]

        if (!$errors && !empty($_FILES['documents']['name'][0])) {
            $allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'webp'];
            $files = $_FILES['documents'];
            $types = $_POST['doc_types'] ?? [];

            if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);

            foreach ($files['name'] as $i => $name) {
                if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed, true)) {
                    $errors[] = "Document \"$name\" : format non autorisé.";
                    continue;
                }
                if ($files['size'][$i] > 5 * 1024 * 1024) {
                    $errors[] = "Document \"$name\" dépasse 5 Mo.";
                    continue;
                }

                $filename = 'doc_' . bin2hex(random_bytes(8)) . '.' . $ext;
                if (move_uploaded_file($files['tmp_name'][$i], UPLOAD_DIR . $filename)) {
                    $uploadedDocs[] = [
                        'type' => $types[$i] ?? 'Autre',
                        'path' => UPLOAD_URL . $filename,
                        'original_name' => $name,
                    ];
                }
            }
        }

    $photoPath = handleClientPhotoUpload('photo');

            if (!$errors) {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare(
                    'INSERT INTO volunteers (user_id, full_name, email, telephone, ville, pays, domaine, disponibilites, competences, experience, motivation, langues, status)
                    VALUES (:user_id, :full_name, :email, :telephone, :ville, :pays, :domaine, :disponibilites, :competences, :experience, :motivation, :langues, "nouveau")'
                );
                $stmt->execute([
                    'user_id' => $client['id'] ?? null,
                    'full_name' => $data['full_name'],
                    'email' => $data['email'],
                    'telephone' => $data['telephone'],
                    'ville' => $data['ville'],
                    'pays' => $data['pays'],
                    'domaine' => $data['domaine'],
                    'disponibilites' => $data['disponibilites'],
                    'competences' => sanitizeRichText($data['competences']),
                    'experience' => sanitizeRichText($data['experience']),
                    'motivation' => sanitizeRichText($data['motivation']),
                    'langues' => $data['langues'],
                ]);

                $volunteerId = (int)$pdo->lastInsertId();

                if ($uploadedDocs) {
                    $insertDoc = $pdo->prepare(
                        'INSERT INTO volunteer_documents (volunteer_id, doc_type, file_path, original_name) VALUES (:vid, :type, :path, :orig)'
                    );
                    foreach ($uploadedDocs as $doc) {
                        $insertDoc->execute([
                            'vid' => $volunteerId,
                            'type' => $doc['type'],
                            'path' => $doc['path'],
                            'orig' => $doc['original_name'],
                        ]);
                    }
                }

                $pdo->commit();
                $success = true;
            }
}

$token = csrfToken();
$pageTitle = 'Devenir bénévole';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-title-area title-bg-three">
    <div class="d-table">
        <div class="d-table-cell">
            <div class="container">
                <div class="title-item">
                    <h2>Devenir bénévole</h2>
                    <ul>
                        <li><a href="index.php">Accueil</a></li>
                        <li><span>Bénévolat</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container ptb-100" style="max-width:800px;">

        <?php if ($success): ?>
            <!-- Popup de confirmation -->
            <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content text-center" style="border-radius:16px; border:none; padding:20px;">
                        <div class="modal-body">
                            <div style="width:80px; height:80px; background:var(--hf-gold); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
                                <i class="icofont-check-alt" style="font-size:40px; color:var(--hf-navy);"></i>
                            </div>
                            <h4 style="color:var(--hf-navy);">Candidature envoyée !</h4>
                            <p class="text-muted">Merci <strong><?= e($data['full_name']) ?></strong>, notre équipe va étudier ta candidature et te recontactera bientôt.</p>
                            <a href="index.php" class="btn common-btn w-100 mt-3">Retour à l'accueil</a>
                        </div>
                    </div>
                </div>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', () => {
                new bootstrap.Modal(document.getElementById('successModal')).show();
            });
            </script>
        <?php else: ?>

        <p class="text-muted mb-4">Rejoins notre réseau de bénévoles et aide-nous à porter nos actions sur le terrain à travers la RD Congo.</p>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= e($token) ?>">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nom complet *</label>
                    <input type="text" name="full_name" class="form-control" required value="<?= e($data['full_name']) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required value="<?= e($data['email']) ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Téléphone *</label>
                    <input type="text" name="telephone" class="form-control" required value="<?= e($data['telephone']) ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Ville</label>
                    <input type="text" name="ville" class="form-control" value="<?= e($data['ville']) ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Pays</label>
                    <input type="text" name="pays" class="form-control" value="<?= e($data['pays']) ?: 'RD Congo' ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Domaine de bénévolat souhaité</label>
                    <select name="domaine" class="form-select">
                        <option value="">— Choisir —</option>
                        <?php foreach (['Éducation', 'Santé', 'Logistique/Terrain', 'Communication/Réseaux sociaux', 'Traduction', 'Collecte de fonds', 'Administratif', 'Autre'] as $d): ?>
                            <option value="<?= e($d) ?>" <?= $data['domaine'] === $d ? 'selected' : '' ?>><?= e($d) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Disponibilités</label>
                    <select name="disponibilites" class="form-select">
                        <option value="">— Choisir —</option>
                        <option value="Quelques heures/semaine" <?= $data['disponibilites'] === 'Quelques heures/semaine' ? 'selected' : '' ?>>Quelques heures/semaine</option>
                        <option value="Week-ends uniquement" <?= $data['disponibilites'] === 'Week-ends uniquement' ? 'selected' : '' ?>>Week-ends uniquement</option>
                        <option value="Temps partiel" <?= $data['disponibilites'] === 'Temps partiel' ? 'selected' : '' ?>>Temps partiel</option>
                        <option value="Temps plein" <?= $data['disponibilites'] === 'Temps plein' ? 'selected' : '' ?>>Temps plein</option>
                        <option value="Ponctuel/événements" <?= $data['disponibilites'] === 'Ponctuel/événements' ? 'selected' : '' ?>>Ponctuel / événements</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Langues parlées</label>
                <input type="text" name="langues" class="form-control" placeholder="Ex: Français, Lingala, Swahili, Anglais" value="<?= e($data['langues']) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Compétences</label>
                <div id="editor-competences" style="background:#fff; min-height:100px;"><?= $data['competences'] ?></div>
                <input type="hidden" name="competences" id="input-competences">
            </div>

            <div class="mb-3">
                <label class="form-label">Expérience (bénévolat, professionnelle...)</label>
                <div id="editor-experience" style="background:#fff; min-height:100px;"><?= $data['experience'] ?></div>
                <input type="hidden" name="experience" id="input-experience">
            </div>

            <div class="mb-3">
                <label class="form-label">Pourquoi veux-tu devenir bénévole chez nous ? *</label>
                <div id="editor-motivation" style="background:#fff; min-height:130px;"><?= $data['motivation'] ?></div>
                <input type="hidden" name="motivation" id="input-motivation">
                <small class="text-danger" id="motivationError" style="display:none;">Ce champ est obligatoire.</small>
            </div>

            <div class="mb-4">
                    <label class="form-label">Documents (CV, pièce d'identité, photo, lettre de motivation, diplômes...)</label>
                    <input type="file" name="documents[]" id="volunteerDocs" class="form-control" multiple accept=".pdf,.doc,.docx,image/*">
                    <small class="text-muted">Tu peux sélectionner plusieurs fichiers à la fois (5 Mo max chacun).</small>

                    <div id="docsPreviewList" class="mt-3"></div>
                </div>

                <script>
                const docsInput = document.getElementById('volunteerDocs');
                const docsPreview = document.getElementById('docsPreviewList');
                const docTypes = ['CV', "Pièce d'identité", 'Photo', 'Lettre de motivation', 'Diplôme/Certificat', 'Autre'];

                docsInput.addEventListener('change', () => {
                    docsPreview.innerHTML = '';
                    Array.from(docsInput.files).forEach((file, index) => {
                        const row = document.createElement('div');
                        row.className = 'd-flex align-items-center mb-2';
                        row.style.gap = '10px';
                        row.innerHTML = `
                            <span style="flex:1; font-size:14px;">📎 ${file.name}</span>
                            <select name="doc_types[]" class="form-select form-select-sm" style="max-width:220px;">
                                ${docTypes.map(t => `<option value="${t}">${t}</option>`).join('')}
                            </select>
                        `;
                        docsPreview.appendChild(row);
                    });
                });
                </script>

                            <div class="text-center mt-4">
                                <button type="submit" class="btn common-btn">Envoyer ma candidature</button>
                            </div>
                        </form>

                    <?php endif; ?>
                </div>

<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
const quillOptions = { theme: 'snow', modules: { toolbar: [['bold','italic','underline'],[{list:'ordered'},{list:'bullet'}],['clean']] } };

const editorCompetences = new Quill('#editor-competences', quillOptions);
const editorExperience = new Quill('#editor-experience', quillOptions);
const editorMotivation = new Quill('#editor-motivation', quillOptions);

document.querySelector('form').addEventListener('submit', (e) => {
    document.getElementById('input-competences').value = editorCompetences.root.innerHTML;
    document.getElementById('input-experience').value = editorExperience.root.innerHTML;
    document.getElementById('input-motivation').value = editorMotivation.root.innerHTML;

    const motivationText = editorMotivation.getText().trim();
    if (!motivationText) {
        e.preventDefault();
        document.getElementById('motivationError').style.display = 'block';
        editorMotivation.root.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>