import TextStyle from '@tiptap/extension-text-style'
import Color from '@tiptap/extension-color'

export default [
    TextStyle.extend({
        priority: 1100,
    }),
    Color.configure({
        types: ['textStyle'],
    }),
]


