# AI Alt Text Generator for WordPress

AI を用いて、WordPress にアップロードされた画像の代替テキスト（alt属性）を自動で生成するプラグインです。Google Gemini や OpenAI の最新の画像認識モデルを活用し、アクセシビリティの向上をサポートします。

## ✨ 主な機能

-   **マルチプロバイダー対応**:
    -   Google Gemini
    -   OpenAI
-   **最新AIモデルの利用**: 各プロバイダーが提供する高性能な画像認識モデルを選択できます。
-   **簡単な操作**:
    -   メディアライブラリの画像リストや、画像詳細画面からワンクリックで代替テキストを生成できます。
    -   すでに代替テキストが存在する場合でも、「再生成」ボタンで内容を更新できます。
-   **言語選択**: 生成する代替テキストの言語を、サイトの言語設定とは別に指定可能です。
-   **柔軟な設定**:
    -   使用するAIプロバイダー、モデル、APIキーをWordPressの管理画面から簡単に設定・変更できます。

## ✅ 要件

-   WordPress 5.0 以上
-   PHP 7.4 以上
-   **Composer**: PHPの依存関係を管理するために必須です。

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
    -   使用したいAIサービス（`Google Gemini` または `OpenAI`）を選択します。
    -   **注意**: プロバイダーを選択するとページが一度リロードされ、選択したプロバイダーに応じた設定項目が表示されます。
3.  **APIキーの入力**:
    -   各サービスの公式サイトで取得したAPIキーを入力してください。
        -   **Google Gemini**: [Google AI Studio](https://aistudio.google.com/app/apikey?hl=ja)
        -   **OpenAI**: [OpenAI Platform](https://platform.openai.com/api-keys)
4.  **モデルの選択**:
    -   代替テキスト生成に使用したいAIモデルを選択します。
5.  **設定を保存**:
    -   「変更を保存」ボタンをクリックします。

## 💡 使い方

設定完了後、`メディア > ライブラリ` を開きます。
代替テキストを生成したい画像の横、または画像の詳細編集画面に表示される **「AIで代替テキスト生成」** または **「AIで代替テキスト再生成」** ボタンをクリックするだけで、自動的に代替テキストが入力されます。

## 👨‍💻 開発者向け

### テストの実行

PHPUnitを使用した単体テストが用意されています。プロジェクトのルートディレクトリで以下のコマンドを実行してください。

```bash
composer test
```

## 📜 ライセンス

[GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html) 