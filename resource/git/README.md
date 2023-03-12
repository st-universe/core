# Git-Hooks für Entwickler

Diese Hooks können genutzt werden, um bestimmte Tasks beim commit und pull zu automatisieren.

## Installation

Entweder die Hooks nach /path/to/stu/core/.git/hooks kopieren oder einen symbolic link erstellen.

Wichtig: `composer` muss im $PATH vorhanden sein.

## pre-commit

Führt automatisch die qa-tests aus bevor der commit durchgeführt wird.

## post-merge

Führt automatisch einen init aus, nachdem Daten via `git pull` abgeholt wurden.