# Product Guidelines

## Brand Voice & Communication

### Tone of Voice
- **Friendly & Approachable**: Use informal Serbian ("ti" form) to create a warm, conversational relationship with users
- Speak as a helpful colleague, not a distant service provider
- Make complex tax and business topics feel simple and manageable
- Use everyday language instead of technical jargon
- Celebrate user successes and acknowledge their challenges

### Writing Principles
- **Clear Over Clever**: Prioritize clarity and understanding over witty copy
- **Action-Oriented**: Use active voice and clear calls-to-action
- **Encouraging**: Frame messages positively, focusing on what users can do rather than limitations
- **Concise**: Respect users' time with brief, scannable content
- **Localized**: Use familiar Serbian business terminology and local examples

### Communication Examples
- ✅ "Hajde da napravimo tvoju prvu fakturu!" (Let's create your first invoice!)
- ✅ "Super! Tvoja faktura je spremna za slanje." (Great! Your invoice is ready to send.)
- ✅ "Pazi, približavaš se godišnjem limitu." (Heads up, you're approaching your annual limit.)
- ❌ "Poštovani korisniče, molimo Vas da popunite obrazac." (Dear user, please fill out the form.)
- ❌ "Greška 403: Nedozvoljen pristup." (Error 403: Access denied.)

## Visual Design Principles

### Design Philosophy
- **Clean & Minimalist**: Embrace white space and simplicity
- Remove all unnecessary elements that don't serve user goals
- Focus attention on primary actions and important information
- Use visual hierarchy to guide users naturally through workflows

### Color Palette
- **Primary Colors**: Use a limited, harmonious color scheme
- Reserve bright colors for important actions and success states
- Use muted tones for secondary information
- Maintain high contrast for readability
- Consistent color coding for recurring elements (e.g., always use the same color for warnings)

### Typography
- **Readable First**: Choose fonts optimized for screen reading
- Establish clear hierarchy with 3-4 font sizes maximum
- Use weight and color for emphasis, not multiple font families
- Ensure minimum 16px font size for body text
- Line height of 1.5 for comfortable reading

### Layout Principles
- **Breathing Room**: Generous padding and margins
- **Visual Grouping**: Related elements stay together
- **Consistent Grid**: Align elements to create order
- **Progressive Complexity**: Simple layouts for simple tasks
- **Focus Points**: One primary action per screen

## User Experience Guidelines

### Error Prevention & Handling

#### Preventive Approach
- **Smart Defaults**: Pre-fill forms with intelligent suggestions
- **Inline Validation**: Check inputs as users type, not after submission
- **Contextual Hints**: Provide help text before users need it
- **Confirmation Dialogs**: Require confirmation for destructive actions
- **Auto-Save**: Prevent data loss with automatic saving

#### Educational Recovery
- **Human-Readable Errors**: Explain what went wrong in plain language
- **Solution-Focused**: Always provide a clear next step
- **Learning Moments**: Include tips to prevent future errors
- **Positive Framing**: "Let's fix this together" instead of "You made an error"
- **Visual Feedback**: Use colors and icons to reinforce messages

### Feature Development Philosophy

#### Progressive Disclosure
- **Start Simple**: Show only essential features to new users
- **Reveal Gradually**: Unlock advanced features as users gain expertise
- **Contextual Options**: Show features when they become relevant
- **Customizable Interface**: Let power users access everything they need
- **Clear Upgrade Paths**: Make it obvious how to access more capabilities

#### Information Architecture
- **Shallow Navigation**: Maximum 3 clicks to any feature
- **Predictable Patterns**: Use familiar UI patterns consistently
- **Clear Labeling**: Descriptive, action-oriented labels
- **Logical Grouping**: Organize features by user tasks, not system architecture
- **Search & Filter**: Help users find what they need quickly

## Accessibility & Inclusivity

### Multi-Device Optimization
- **Responsive Design**: Fluid layouts that adapt to any screen size
- **Touch-Friendly**: Minimum 44x44px touch targets on mobile
- **Desktop-Optimized**: Keyboard shortcuts and hover states for power users
- **Tablet Experience**: Balanced interface between mobile and desktop
- **Consistent Experience**: Core features work identically across devices

### Performance Standards
- **Fast Load Times**: Initial page load under 2 seconds
- **Smooth Interactions**: 60fps animations and transitions
- **Offline Capability**: Basic features available without connection
- **Progressive Enhancement**: Core functionality works on all devices
- **Optimized Assets**: Compressed images and lazy loading

## Interaction Patterns

### Form Design
- **Single Column**: One field per row for mobile compatibility
- **Logical Flow**: Group related fields together
- **Clear Labels**: Always visible, positioned above fields
- **Helpful Placeholders**: Show format examples, not repeat labels
- **Progress Indicators**: Show steps in multi-page forms

### Feedback & Notifications
- **Immediate Response**: Acknowledge every user action
- **Appropriate Interruption**: Use modals sparingly
- **Persistent Important Info**: Keep critical messages visible
- **Dismissible Notices**: Let users control non-critical notifications
- **Success Celebration**: Acknowledge accomplishments with subtle delight

### Data Visualization
- **Clarity Over Decoration**: Simple charts that communicate clearly
- **Interactive Elements**: Hover/tap for detailed information
- **Meaningful Colors**: Use color to convey information, not just decoration
- **Accessible Alternatives**: Provide data tables alongside visualizations
- **Real-Time Updates**: Show live data changes smoothly

## Content Guidelines

### Microcopy
- **Button Labels**: Use verbs that describe the action ("Pošalji fakturu" not "OK")
- **Empty States**: Friendly messages with clear next steps
- **Loading States**: Inform users what's happening
- **Confirmation Messages**: Clear description of what will happen
- **Helper Text**: Brief, scannable instructions

### Documentation
- **Task-Based**: Organize help by what users want to accomplish
- **Visual Aids**: Use screenshots and videos liberally
- **Quick Start Guides**: Get users to success fast
- **Searchable**: Comprehensive search with filters
- **Version Control**: Keep documentation synchronized with features

## Quality Standards

### Testing Requirements
- **Cross-Device Testing**: Verify on iOS, Android, Windows, macOS
- **Browser Compatibility**: Support last 2 versions of major browsers
- **User Testing**: Validate with real pausalni entrepreneurs
- **Accessibility Audit**: Regular checks for inclusivity
- **Performance Monitoring**: Track and optimize load times

### Review Checklist
Before any feature release:
- [ ] Works on mobile devices
- [ ] Error messages are helpful and friendly
- [ ] Loading states are present
- [ ] Empty states guide users
- [ ] Forms validate inline
- [ ] Success feedback is clear
- [ ] Help text is available
- [ ] Design follows minimalist principles