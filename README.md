# AFblog — 一个小白且极具特色的笔记 PHP 网站

> **标语 / 一句话简介**  
> 一个简陋、混乱但怀揣热情的笔记平台，用于记录、展示与练手 — 用 PHP 写成的轻量博客 / 个人笔记系统


[![zread](https://img.shields.io/badge/Ask_Zread-_.svg?style=for-the-badge&color=00b0aa&labelColor=000000&logo=data%3Aimage%2Fsvg%2Bxml%3Bbase64%2CPHN2ZyB3aWR0aD0iMTYiIGhlaWdodD0iMTYiIHZpZXdCb3g9IjAgMCAxNiAxNiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTQuOTYxNTYgMS42MDAxSDIuMjQxNTZDMS44ODgxIDEuNjAwMSAxLjYwMTU2IDEuODg2NjQgMS42MDE1NiAyLjI0MDFWNC45NjAxQzEuNjAxNTYgNS4zMTM1NiAxLjg4ODEgNS42MDAxIDIuMjQxNTYgNS42MDAxSDQuOTYxNTZDNS4zMTUwMiA1LjYwMDEgNS42MDE1NiA1LjMxMzU2IDUuNjAxNTYgNC45NjAxVjIuMjQwMUM1LjYwMTU2IDEuODg2NjQgNS4zMTUwMiAxLjYwMDEgNC45NjE1NiAxLjYwMDFaIiBmaWxsPSIjZmZmIi8%2BCjxwYXRoIGQ9Ik00Ljk2MTU2IDEwLjM5OTlIMi4yNDE1NkMxLjg4ODEgMTAuMzk5OSAxLjYwMTU2IDEwLjY4NjQgMS42MDE1NiAxMS4wMzk5VjEzLjc1OTlDMS42MDE1NiAxNC4xMTM0IDEuODg4MSAxNC4zOTk5IDIuMjQxNTYgMTQuMzk5OUg0Ljk2MTU2QzUuMzE1MDIgMTQuMzk5OSA1LjYwMTU2IDE0LjExMzQgNS42MDE1NiAxMy43NTk5VjExLjAzOTlDNS42MDE1NiAxMC42ODY0IDUuMzE1MDIgMTAuMzk5OSA0Ljk2MTU2IDEwLjM5OTlaIiBmaWxsPSIjZmZmIi8%2BCjxwYXRoIGQ9Ik0xMy43NTg0IDEuNjAwMUgxMS4wMzg0QzEwLjY4NSAxLjYwMDEgMTAuMzk4NCAxLjg4NjY0IDEwLjM5ODQgMi4yNDAxVjQuOTYwMUMxMC4zOTg0IDUuMzEzNTYgMTAuNjg1IDUuNjAwMSAxMS4wMzg0IDUuNjAwMUgxMy43NTg0QzE0LjExMTkgNS42MDAxIDE0LjM5ODQgNS4zMTM1NiAxNC4zOTg0IDQuOTYwMVYyLjI0MDFDMTQuMzk4NCAxLjg4NjY0IDE0LjExMTkgMS42MDAxIDEzLjc1ODQgMS42MDAxWiIgZmlsbD0iI2ZmZiIvPgo8cGF0aCBkPSJNNCAxMkwxMiA0TDQgMTJaIiBmaWxsPSIjZmZmIi8%2BCjxwYXRoIGQ9Ik00IDEyTDEyIDQiIHN0cm9rZT0iI2ZmZiIgc3Ryb2tlLXdpZHRoPSIxLjUiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIvPgo8L3N2Zz4K&logoColor=ffffff)](https://zread.ai/Aftnos/AFblog)

---

## 项目背景  

AFblog 是为练习与记录而写的一个非常简易的笔记 / 博客系统，使用纯 PHP无框架实现在轻巧、易修改、易理解。

---

## 功能特色  

以下是 AFblog 当前（或计划）具备的主要特性：

- 文章 / 笔记的发布、编辑、删除  
- 搜索 / 分类 /标签 /归档功能  
- 用户登录 /后台管理（基础）  
- 图片 / 视频 上传功能  
- 站内搜索功能  
- 站点地图（sitemap）生成  
- 响应式 / 简易前端样式支持  
- 错误页面 / 异常处理  


---

## 演示 / 截图  

 
> ![首页截图](path/to/screenshot1.png)  
> ![后台管理截图](path/to/screenshot2.png)  

---

## 环境要求  

在部署之前，请确认以下环境满足：

| 组件 | 建议版本 | 最低要求 |
|------|----------|------------|
| PHP | ≥ 7.2 | emm...7.2|
| Web 服务器 | Nginx | Apache |
| 数据库 | MySQL8 | 支持UTF-8mb4的MySQL|
| 扩展 / 模块 | GD / DOM / PDO / cURL| PDO |
| 文件读写权限 | 存储上传目录可写权限 | 乱给吧777 |

> ⚠️ 注意：数据库一定要UTF-8mb4

## 安装与部署  

下面是一个基本的安装 / 部署流程示例：

1. 克隆 / 下载代码  
   ```bash
   git clone https://github.com/Aftnos/AFblog.git
   cd AFblog
````

2. 上传到你服务器的 Web 根目录或子目录。

3. 配置环境（见下文的 “配置说明” 部分）。

4. 设置目录权限：

   ```bash
   chmod -R 755 uploads/ cache/ logs/
   # 或根据你项目里真正需要写权限的目录设置
   ```

5. 如果使用数据库：导入 SQL 脚本（若有提供），并填入数据库连接配置。

6. 访问站点：使用浏览器访问相应地址，看是否能正常访问首页、后台等。

---

## 目录结构

下面是 AFblog 当前的目录 / 文件结构：

```
AFblog/
├── admin/  
├── config/  
├── css/  
├── error/  
├── js/  
├── LICENSE  
├── an.php  
├── bj.php  
├── content.php  
├── cs.php  
├── favicon.ico  
├── index.php  
├── lizi.php  
├── login.php  
├── other_essence.php  
├── search.php  
├── sitemap.php  
├── tou.php  
├── upload_image.php  
├── upload_video.php  
├── wei.php  
├── xx.php  
└── README.m
```

## 扩展与定制

如果你或其他人要在 AFblog 基础上做扩展、重构或二次开发，可以参考以下建议：

* 引入简单的 MVC 结构 / 路由系统
* 使用 Composer / PSR-4 自动加载
* 使用模板引擎（如 Twig / Blade）分离逻辑与视图
* 加入缓存机制（文件缓存 / Redis / Memcached）
* 增加用户 / 权限系统（多个用户、权限等级）
* 优化安全（防止 SQL 注入、XSS、CSRF 等）
* 增加动态主题 / 插件机制
* 重构代码风格、命名、代码可读性、模块划分等

---

## 已知问题 / 限制

（在这里诚实地写出当前版本存在的问题、尚未支持的功能、性能瓶颈等）

* 代码结构混乱，缺乏模块化
* 错误处理不完善，可能出现未捕获异常
* 并发 / 大流量下性能尚未优化
* 上传文件安全性（文件类型校验 / 防止覆盖 / 重名冲突等）
* URL 路由 / 伪静态支持可能不充足
* 多用户 / 权限管理尚未完整设计

---

## 参与 & 贡献

非常欢迎你提 Issue、PR、Fork 或在 Star 中支持我！下面是贡献流程建议：

1. Fork 本仓库
2. 新建 Feature 分支：`git checkout -b feature/你的功能`
3. 在分支上进行修改、测试
4. 提交 Commit 并推送到你的 fork
5. 向主仓库发起 Pull Request，说明你做了什么、为什么这样做
6. 我会尽可能审查、合并或给出反馈

也欢迎就功能、重构、优化等方面提出建议或讨论。

---

## 许可证

本项目采用 **Apache License 2.0** 许可证。详情请参见仓库中的 [LICENSE](./LICENSE) 文件。 ([GitHub][1])

---

## 致谢

如果你有好的建议、改进或 bug 报告，欢迎随时提 Issue 或 PR。
---

**备注：**
上述 README 模板为通用版本，你可以删改、增补或美化（加入徽章、状态、CI 构建状态、部署脚本等）以适应你项目的需要。
如果你愿意的话，我可以帮你根据 AFblog 的代码，更“贴合实际”的 README 版本，你要吗？

[1]: https://github.com/Aftnos/AFblog "GitHub - Aftnos/AFblog: 一个小白且极具特色的笔记PHP网站，低级且混乱"
