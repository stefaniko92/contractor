# Claude Code Global Quick Reference

## 🎯 Current Setup

- **Global Model**: `claude` (Claude Sonnet 4.5)
- **API URLs**:
  - Claude: `https://api.z.ai/api/anthropic`
  - GLM: `https://api.z.ai/api/glm`
- **Project Override**: Not set (using global)

## 🚀 Quick Commands

### Global Model Management
```bash
claude-global global claude     # Set global to Claude Sonnet 4.5
claude-global global glm        # Set global to GLM 4.6
claude-global global-switch     # Interactive selection
```

### Project-Specific Override
```bash
claude-local local glm           # Use GLM for this project only
claude-local local-clear         # Remove override (use global)
```

### Status & Help
```bash
claude-global status              # Show current configuration
claude-global --help             # Show all commands
```

## 📋 Model Information

| Model | API URL | Best For |
|-------|---------|----------|
| claude | https://api.z.ai/api/anthropic | Programming, code analysis |
| glm | https://api.z.ai/api/glm | Creative writing, general conversation |

## 🔧 Configuration Files

```
~/.claude/
├── settings.json              # API configuration and keys
├── global_model              # Current global model (claude/glm)
└── current_model             # Project-specific override (optional)
```

## 💡 Usage Tips

1. **Set once, use everywhere**: `claude-global global claude`
2. **Override when needed**: `claude-global local glm` (for this project)
3. **Check status often**: `claude-global status`

---

*All project-specific scripts have been removed. Use the global `claude-global` command from any directory.*