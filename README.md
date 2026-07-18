# AI Alt Text Generator for WordPress

AI を用いて、WordPress にアップロードされた画像の代替テキスト（alt属性）を自動で生成するプラグインです。Google Gemini や OpenAI の最新の画像認識モデルを活用し、アクセシビリティの向上をサポートします。

## ✨ 主な機能

-   **マルチプロバイダー対応**:
    -   Google Gemini
    -   OpenAI
    -   Anthropic Claude
-   **最新AIモデルの利用**: 各プロバイダーが提供する高性能な画像認識モデルを選択できます。
-   **簡単な操作**:
    -   メディアライブラリの画像リストや、画像詳細画面からワンクリックで代替テキストを生成できます。
    -   すでに代替テキストが存在する場合でも、「再生成」ボタンで内容を更新できます。
-   **言語選択**: 生成する代替テキストの言語を、サイトの言語設定とは別に指定可能です。
-   **柔軟な設定**:
    -   使用するAIプロバイダー、モデル、APIキーをWordPressの管理画面から簡単に設定・変更できます。
    -   代替テキストの生成言語は、日本語・英語・中国語・韓国語・フランス語・ドイツ語から選択できます（`設定 > AI Alt Text`）。

## ✅ 要件

-   WordPress 5.0 以上
-   PHP 7.4 以上（`composer.json` の `require` に準拠）
-   **Composer**: PHPの依存関係を管理するために必須です。

各プロバイダーで既定として使用されるモデルは以下のとおりです（管理画面から変更可能）。

| プロバイダー | 既定モデル |
| --- | --- |
| Google Gemini | `models/gemini-2.5-flash-lite-preview-06-17` |
| OpenAI | `gpt-4.1-mini-2025-04-14` |
| Anthropic Claude | `claude-3-5-haiku-latest` |

## 🚀 インストール

1.  **リポジトリをクローン**:
    お使いのWordPress環境の `wp-content/plugins` ディレクトリに、このリポジトリをクローンします。
    ```bash
    cd /path/to/your/wordpress/wp-content/plugins
    git clone https://github.com/ozekimasaki/ai-alt-text-generator.git
    ```

2.  **依存関係をインストール**:
    プラグインのディレクトリに移動し、`Composer` を使って必要なライブラリをインストールします。
    ```bash
    cd ai-alt-text-generator
    composer install
    ```

3.  **プラグインを有効化**:
    WordPressの管理画面にログインし、「プラグイン」メニューから「AI Alt Text Generator」を有効化してください。

## 🔧 設定

1.  WordPress管理画面の `設定 > AI Alt Text` に移動します。
2.  **AI プロバイダーの選択**:
    -   使用したいAIサービス（`Google Gemini`、`OpenAI`、または `Anthropic Claude`）を選択します。
    -   **注意**: プロバイダーを選択するとページが一度リロードされ、選択したプロバイダーに応じた設定項目が表示されます。
3.  **APIキーの入力**:
    -   各サービスの公式サイトで取得したAPIキーを入力してください。
        -   **Google Gemini**: [Google AI Studio](https://aistudio.google.com/app/apikey?hl=ja)
        -   **OpenAI**: [OpenAI Platform](https://platform.openai.com/api-keys)
        -   **Anthropic Claude**: [Anthropic Console](https://console.anthropic.com/)
4.  **モデルの選択**:
    -   代替テキスト生成に使用したいAIモデルを選択します。
5.  **設定を保存**:
    -   「変更を保存」ボタンをクリックします。

## 💡 使い方

設定完了後、`メディア > ライブラリ` を開きます。
代替テキストを生成したい画像の横、または画像の詳細編集画面に表示される **「AIで代替テキスト生成」** または **「AIで代替テキスト再生成」** ボタンをクリックするだけで、自動的に代替テキストが入力されます。

## 🗂 ディレクトリ構成

```
ai-alt-text-generator/
├── ai-alt-text-generator.php   # プラグインのエントリポイント（ヘッダー・オートロード・ブートストラップ）
├── includes/                   # プラグイン本体（PSR-4: AiAltText\ 名前空間）
│   ├── Plugin.php              # コアクラス。フック登録・管理画面連携・生成ボタン追加
│   ├── Container.php           # 簡易 DI コンテナ（サービス登録・取得）
│   ├── Config.php              # オプション取得とプロバイダー依存の設定解決
│   ├── Constants.php           # オプション名・既定モデル・対応言語などの定数
│   ├── Settings.php            # 設定ページ（設定 > AI Alt Text）の描画と登録
│   ├── AjaxController.php       # Ajax エンドポイント（単一画像の生成）
│   ├── AIProviderInterface.php # AI プロバイダー共通インターフェース
│   ├── GeminiProvider.php      # Google Gemini 実装
│   ├── OpenAIProvider.php      # OpenAI 実装
│   ├── ClaudeProvider.php      # Anthropic Claude 実装
│   └── Logger.php              # ロギングと WP_Error 生成のヘルパー
├── assets/js/admin.js          # メディア画面の生成ボタン用スクリプト
├── tests/                      # PHPUnit テスト（bootstrap で WP 関数をモック）
├── composer.json               # 依存関係・オートロード・スクリプト定義
└── phpunit.xml.dist            # PHPUnit 設定
```

## 👨‍💻 開発者向け

### アーキテクチャ概要

-   `AiAltText\Plugin::init()` を `plugins_loaded` フックで起動し、`Container` にサービスを登録します。
-   AI プロバイダーは `AIProviderInterface` を実装し、`Config::get_provider_class()` が選択中のプロバイダークラスを解決します。
-   代替テキストの生成は `admin-ajax.php` 経由（アクション `ai_generate_alt`）で単一画像に対して実行されます。

### テストの実行

PHPUnitを使用した単体テストが用意されています。まず `composer install` で依存関係を導入し、プロジェクトのルートディレクトリで以下のコマンドを実行してください。

```bash
composer install
composer test
```

`composer test` は `vendor/bin/phpunit` を実行します（`phpunit.xml.dist` の設定を使用）。

## 📜 ライセンス

[GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html) 