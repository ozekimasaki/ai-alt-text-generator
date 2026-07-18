# AGENTS.md

このリポジトリで作業するコーディングエージェント向けのガイドです。実在するファイル・コマンド・規約のみを記載しています。

## プロジェクト概要

AI（Google Gemini / OpenAI / Anthropic Claude）を用いて、WordPress のメディアに登録された画像の代替テキスト（alt 属性）を自動生成する WordPress プラグインです。PHP で実装され、Composer で依存関係を管理します。

## プロジェクト構成 / エントリポイント

- `ai-alt-text-generator.php`: プラグインのエントリポイント。プラグインヘッダー、定数定義、Composer オートロードの読み込み、`plugins_loaded` での `AiAltText\Plugin::init()` 起動を行う。
- `includes/`: プラグイン本体。PSR-4 で名前空間 `AiAltText\` にマッピング（`composer.json` の `autoload`）。
  - `Plugin.php`: コアクラス。フック登録、管理画面スクリプトの enqueue、添付編集画面への生成ボタン追加。
  - `Container.php`: 簡易 DI コンテナ。`ai_provider` / `settings` / `ajax_controller` を登録・取得。
  - `Config.php`: `get_option` のラッパーと、選択中プロバイダーに応じた言語・モデル・オプションキーの解決。
  - `Constants.php`: オプション名、既定モデル、対応プロバイダー・言語、Ajax アクション名、nonce などの定数。
  - `Settings.php`: 設定ページ（`設定 > AI Alt Text`）の登録・描画。
  - `AjaxController.php`: Ajax エンドポイント。現状は単一画像の生成（アクション `ai_generate_alt`）のみ実装。
  - `AIProviderInterface.php`: 各 AI プロバイダーが実装する共通インターフェース。
  - `GeminiProvider.php` / `OpenAIProvider.php` / `ClaudeProvider.php`: 各プロバイダー実装（シングルトン）。
  - `Logger.php`: ロギングおよび `WP_Error` 生成のヘルパー。
- `assets/js/admin.js`: メディア画面の生成ボタンから Ajax を呼び出すスクリプト。
- `tests/`: PHPUnit テスト。`tests/bootstrap.php` で `get_option` などの WordPress 関数をモックし、`tests/Unit/` にテストを配置。
- `composer.json` / `composer.lock`: 依存関係・オートロード・スクリプト定義。
- `phpunit.xml.dist`: PHPUnit 設定（`bootstrap="tests/bootstrap.php"`、テストスイート `unit` は `tests` ディレクトリ）。

## セットアップ

前提: PHP 7.4 以上（`composer.json` の `require`）と Composer。

```bash
composer install
```

依存関係は `vendor/` に導入され、`.gitignore` により Git 管理対象外です。WordPress 環境で動作させる場合は、本ディレクトリを `wp-content/plugins/` 配下に配置し、管理画面からプラグインを有効化してください。

## ビルド / テスト / lint / typecheck コマンド

- ビルド: 専用のビルド手順はありません（`composer install` で依存導入のみ）。
- テスト:

  ```bash
  composer test
  ```

  これは `composer.json` の `scripts.test` に定義された `vendor/bin/phpunit` を実行します。直接実行も可能です:

  ```bash
  vendor/bin/phpunit
  ```

- lint / typecheck: 専用の lint・静的解析・型チェックツールはこのリポジトリに設定されていません（`composer.json` の `require-dev` は `phpunit/phpunit` のみ）。設定を追加せずに独自ツールを持ち込まないこと。文法確認が必要な場合は `php -l <file>` を使用できます。

## コーディング規約

- 言語・スタイル: 既存コードに合わせる。PHP ファイルは名前空間 `AiAltText\`、クラス名は PascalCase、メソッド・関数はスネークケース（例: `handle_single`、`get_provider_class`）、定数は大文字スネークケース。
- WordPress の慣習に従う: 出力は `esc_html` / `esc_attr` / `esc_js` などでエスケープし、Ajax では `check_ajax_referer` と `current_user_can` で権限・nonce を検証する。
- 文字列は国際化する: ユーザー向け文字列は `__()` / `esc_html__()` などでラップし、テキストドメインには `Constants::TEXT_DOMAIN`（`ai-alt-text`）を使用する。
- 設定値・オプション名・モデル名などのマジックストリングは直接書かず、`Constants` の定数を参照する。
- 新しい AI プロバイダーを追加する場合は `AIProviderInterface` を実装し、`Config::get_provider_class()` のマップと `Constants::AVAILABLE_PROVIDERS` / `AVAILABLE_MODELS` に登録する。
- DocBlock コメントは既存ファイルのスタイルに合わせて記述する。

## 注意点

- `vendor/` はコミットしない（`.gitignore` 済み）。生成ファイルを手で編集しない。
- API キーなどの機密情報をコード・コミットに含めない。API キーは WordPress のオプションとして保存される。
- 一括生成（bulk）は未実装。`Constants` に `AJAX_GENERATE_BULK` や `SCRIPT_HANDLE_BULK` の定義は存在するが、`AjaxController::init()` は単一生成のみ登録している。実在しない機能として扱うこと。
- テストは `tests/bootstrap.php` で WordPress 関数をモックしている。WordPress 関数に依存するコードをテストする際は、必要に応じてモックを追加する。
- 変更後は必ず `composer test` を実行して既存テストが通ることを確認する。
