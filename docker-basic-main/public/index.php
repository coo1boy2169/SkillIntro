<?php
$host = getenv('DB_HOST') ?: 'mariadb';
$db   = getenv('DB_NAME') ?: 'skillsopdracht';
$user = getenv('DB_USERNAME') ?: 'web';
$pass = getenv('DB_PASSWORD') ?: 'admin';
$port = 3306;
$conn = new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) {
    die("Verbinding mislukt: " . $conn->connect_error);
}

$sql = "SELECT * FROM projects";
$result = $conn->query($sql);
if (!$result) {
    die("Query fout: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projecten</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        body {
            background: #EEAECA;
            background: radial-gradient(circle, rgba(238, 174, 202, 1) 0%, rgba(148, 187, 233, 1) 100%);
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 40px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 200px 1fr;
            grid-template-rows: auto 1fr auto;
            gap: 40px;
            min-height: 80vh;
        }


        .logo img {
            max-width: 100%;
            max-height: 100%;
        }

        .projects-grid {
            grid-column: 2;
            grid-row: 1 / 3;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            align-content: start;
        }

        .project {
            background-color: #999;
            min-height: 250px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: white;
            position: relative;
            overflow: hidden;
            cursor: grab;
            transition: transform 0.2s ease;
        }

        .project:active {
            cursor: grabbing;
        }

        .project.dragging {
            opacity: 0.5;
            transform: rotate(5deg);
            z-index: 1000;
        }

        .project.drag-over {
            border: 2px dashed #fff;
            transform: scale(1.02);
        }

        .project h2 {
            font-size: 18px;
            margin-bottom: 8px;
            z-index: 2;
            position: relative;
        }

        .project p {
            font-size: 14px;
            margin-bottom: 15px;
            z-index: 2;
            position: relative;
        }

        .project .images {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.4;
        }

        .project img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
            transition: opacity 0.5s ease;
        }

        .project .image-main {
            opacity: 1;
            z-index: 1;
        }

        .project .image-second {
            opacity: 0;
            z-index: 0;
        }

        .project:hover .image-main {
            opacity: 0;
        }

        .project:hover .image-second {
            opacity: 1;
        }

        .project:hover .images {
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }

        .contact-info {
            grid-column: 1;
            grid-row: 4;
            align-self: end;
            font-size: 14px;
            line-height: 1.4;
            color: #333;
        }

        .contact-info h3 {
            margin-bottom: 20px;
            font-size: 16px;
        }

        .no-projects {
            grid-column: 2;
            grid-row: 1 / 3;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #666;
        }

        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
                grid-template-rows: auto auto auto;
                gap: 20px;
            }

            .logo {
                grid-column: 1;
                grid-row: 1;
                width: 100%;
                height: 80px;
            }

            .projects-grid {
                grid-column: 1;
                grid-row: 2;
                grid-template-columns: 1fr;
            }

            .contact-info {
                grid-column: 1;
                grid-row: 3;
            }

            .no-projects {
                grid-column: 1;
                grid-row: 2;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="logo">
            <img src="/assets/img/projects/logo-livingshapes.png" alt="Living Shapes Logo">
        </div>
        <?php if ($result->num_rows > 0): ?>
            <div class="projects-grid">
                <?php
                $index = 0;
                while ($row = $result->fetch_assoc()): ?>
                    <div class="project" draggable="true" data-project="<?= $index ?>" data-id="<?= $row['id'] ?>">
                        <div>
                            <h2><?= htmlspecialchars($row['title']) ?></h2>
                            <p><?= htmlspecialchars($row['city_location']) ?></p>
                        </div>
                        <div class="images">
                            <img class="image-main" src="/assets/img/projects/<?= htmlspecialchars($row['image_main']) ?>" alt="<?= htmlspecialchars($row['title']) ?> - hoofd afbeelding">
                            <img class="image-second" src="/assets/img/projects/<?= htmlspecialchars($row['image_second']) ?>" alt="<?= htmlspecialchars($row['title']) ?> - tweede afbeelding">
                        </div>
                    </div>
                <?php
                    $index++;
                endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-projects">
                <p>Geen projecten gevonden.</p>
            </div>
        <?php endif; ?>
        <div class="contact-info">
            <h3>interested?</h3>
            <p>Drop us a email at:</p>
            <p>newbusiness@livingshaper.eu</p>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let draggedElement = null;
            let draggedData = null;
            const projects = document.querySelectorAll('.project');
            projects.forEach(project => {
                project.addEventListener('dragstart', function(e) {
                    draggedElement = this;
                    const mainImg = this.querySelector('.image-main');
                    const secondImg = this.querySelector('.image-second');
                    const title = this.querySelector('h2').textContent;
                    const location = this.querySelector('p').textContent;
                    draggedData = {
                        mainSrc: mainImg.src,
                        mainAlt: mainImg.alt,
                        secondSrc: secondImg.src,
                        secondAlt: secondImg.alt,
                        title: title,
                        location: location,
                        projectId: this.dataset.id
                    };
                    this.classList.add('dragging');
                    e.dataTransfer.effectAllowed = 'move';
                });
                project.addEventListener('dragend', function(e) {
                    this.classList.remove('dragging');
                    document.querySelectorAll('.project').forEach(p => p.classList.remove('drag-over'));
                    draggedElement = null;
                    draggedData = null;
                });
                project.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    if (this !== draggedElement) {
                        e.dataTransfer.dropEffect = 'move';
                        this.classList.add('drag-over');
                    }
                });
                project.addEventListener('dragleave', function(e) {
                    this.classList.remove('drag-over');
                });
                project.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.classList.remove('drag-over');
                    if (draggedElement !== this && draggedElement !== null) {
                        const targetMainImg = this.querySelector('.image-main');
                        const targetSecondImg = this.querySelector('.image-second');
                        const targetTitle = this.querySelector('h2');
                        const targetLocation = this.querySelector('p');
                        const targetData = {
                            mainSrc: targetMainImg.src,
                            mainAlt: targetMainImg.alt,
                            secondSrc: targetSecondImg.src,
                            secondAlt: targetSecondImg.alt,
                            title: targetTitle.textContent,
                            location: targetLocation.textContent,
                            projectId: this.dataset.id
                        };
                        targetMainImg.src = draggedData.mainSrc;
                        targetMainImg.alt = draggedData.mainAlt;
                        targetSecondImg.src = draggedData.secondSrc;
                        targetSecondImg.alt = draggedData.secondAlt;
                        targetTitle.textContent = draggedData.title;
                        targetLocation.textContent = draggedData.location;
                        this.dataset.id = draggedData.projectId;
                        const draggedMainImg = draggedElement.querySelector('.image-main');
                        const draggedSecondImg = draggedElement.querySelector('.image-second');
                        const draggedTitle = draggedElement.querySelector('h2');
                        const draggedLocation = draggedElement.querySelector('p');
                        draggedMainImg.src = targetData.mainSrc;
                        draggedMainImg.alt = targetData.mainAlt;
                        draggedSecondImg.src = targetData.secondSrc;
                        draggedSecondImg.alt = targetData.secondAlt;
                        draggedTitle.textContent = targetData.title;
                        draggedLocation.textContent = targetData.location;
                        draggedElement.dataset.id = targetData.projectId;
                    }
                });
            });
            document.addEventListener('dragover', function(e) {
                e.preventDefault();
            });
            document.addEventListener('drop', function(e) {
                e.preventDefault();
            });
        });
    </script>
</body>

</html>
<?php
$conn->close();
?>